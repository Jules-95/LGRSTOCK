<?php

/**
 * Controller des sessions d'inventaire
 * Le dépot vient toujours de la session 
 * Appelle le Model Inventory.php et renvoie du JSON
 */

require_once __DIR__ . '/../Models/Inventory.php';

class InventoryController
{
    private Inventory $inventoryModel;

    public function __construct($pdo)
    {
        $this->inventoryModel = new Inventory($pdo);
    }

    // METHODES PUBLIQUES (Une par endpoint)

    /**
     * Inventaire en cours du dépot de l'utilisateur (data = null si aucun)
     * Utilisé par : GET /api/Inventory/status.php
     */

    public function statut() {
        try {
            $depot = $_SESSION['magasin'] ?? null;

            $this->sendResponse(200, [
                'error' => false,
                'data'  => $this->inventoryModel->getEnCours($depot)
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Démarer un inventaire (remise à 0 du dépot)
     * Utilisé par : POST /api/inventory/start.php
     */

    public function demarrer() {
        try {
            $depot = $_SESSION['magasin'] ?? null;
            $userId = $_SESSION['user_id'] ?? null;

            $inventory = $this->inventoryModel->demarrer($depot, $userId);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Inventaire démarré, stock du dépôt remis à 0',
                'data' => $inventory,
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Termine l'inventaire en cours 
     * Utilisé par : POST /api/Inventory/finish.php
     */
    public function terminer() {
        try {
            $depot = $_SESSION['magasin'] ?? null;

            $this->inventoryModel->terminer($depot);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Inventaire terminé'
            ]);
        } catch(Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    // METHODES PRIVATE

    private function sendResponse($statusCode, $data)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}