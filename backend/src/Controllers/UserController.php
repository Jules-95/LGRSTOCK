<?php

/**
 * Controller qui gère les requêtes HTTP liées aux utilisateurs : 
 * - Récupère les données de la requêtes ($_GET, $_POST)
 * - Appelle le Model User.php
 * - Formate et renvoie les réponses JSON
 */

require_once __DIR__ . '/../Models/User.php';

class UserController {
    private User $userModel;

    public function __construct(PDO $pdo) {
        $this->userModel =new User($pdo);
    }

    /**
     * Retourne la liste de tous les utilisateurs
     * Utilisé par : GET /api/User/users.php
     */
    public function getAllUser(): void {
        try {
            $users = $this->userModel->getAllUser();

            $this->sendResponse(200, [
                'error' => false,
                'count' => count($users),
                'data'  => $users
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'error'   => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Création d'un nouvel utilisateur 
     * Utilisé par : POST /api/User/add-user.php
     */
    public function addUser(): void {
        try {
            $newId = $this->userModel->createUser([
                'username' =>$_POST['username'] ?? '',
                'password' =>$_POST['password'] ?? '',
                'role'     =>$_POST['role']     ?? '',
                'magasin'  =>$_POST['magasin']  ?? '',
            ]);

            $this->sendResponse(200, [
                'error'   => false,
                'message' => 'Utilisateur créé avec succès',
                'id'      => $newId
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Modification d'un utilisateur existant
     * Utilisé par : POST /api/User/edit-user.php
     */
    public function editUser(): void {
        try {
            $id = (int) ($_POST['id'] ?? 0);

            $this->userModel->updateUser($id, [
                'username' => $_POST['username'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role'     => $_POST['role']     ?? '',
                'magasin'  => $_POST['magasin']  ?? '',
            ]);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Utilisateur modifié avec succès'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Suppression d'un utilisateur 
     * Utilisé par : POST /api/User/delete-user.php
     */
    public function deleteUser(): void {
        try {
            $id            = (int) ($_POST['id'] ?? 0);
            $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

            $this->userModel->deleteUser($id, $currentUserId);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error'   => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Renvoit d'une réponse JSON avec code HTTP associé
     */
    private function sendResponse(int $statusCode, array $data): void {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}