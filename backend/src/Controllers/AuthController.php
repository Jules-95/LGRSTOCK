<?php 

/** 
 * ============================================================
 * CONTROLLER : AUTHENTIFICATION
 * ============================================================
 * 
 * CE QU'IL RECOIT : 
 * - Une instance PDO pour accéder à la bdd 
 * 
 * CE QU'IL UTILISE : 
 * - User.php (Model)      -> Cherche l'utilisateur en base et vérifie le mdp
 * - Auth.php (Middleware) -> Démare et détruit la session
 * 
 * CE QU'IL FAIT : 
 * - login()     -> Vérifie les indentifiants, démare la session 
 * - logout()    -> Détruit la session active 
 * - checkAuth() -> Lit $_SESSION et retourne les infos connectés
 * 
 * CE QU'IL DESSERT : 
 * - api/login.php 
 * - api/logout.php 
 * - api/check-auth.php 
 * 
 * RESUME : 
 * - Reçoit  -> Crédentiales (login) / cookie de session (logout/check)
 * - Traite  -> Vérifie identité, gère la session 
 * - Produit -> Réponse JSON avec statut et infos utilisateur.  
 */

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Middleware/Auth.php';

class AuthController {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Verifie les identifiants et démare la session si corrects.
     * Appelé par api/login.php 
     */
public function login(): void {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $ip = $_SERVER['REMOTE_ADDR'];

    // Nettoyage des vieilles tentatives (> 1 jour)
    $this->pdo->exec("DELETE FROM login_attempts WHERE created_at < NOW() - INTERVAL 1 DAY");

    // Compter les tentatives des 15 dernières minutes
    $stmt = $this->pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts 
         WHERE ip = :ip AND created_at > NOW() - INTERVAL 15 MINUTE"
    );
    $stmt->execute([':ip' => $ip]);
    $attempts = $stmt->fetchColumn();

    // Bloquer si 5 tentatives ou plus
    if ($attempts >= 5) {
        http_response_code(429);
        echo json_encode(['error' => true, 'message' => 'Trop de tentatives, réessayez dans 15 minutes']);
        exit;
    }

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Identifiants manquants']);
        return;
    }

    try {
        $userModel = new User($this->pdo);
        $found     = $userModel->findByUsername($username);

        if (!$found || !$userModel->verifyPassword($password, $found['password_hash'])) {
            // Enregistrer la tentative échouée
            $stmt = $this->pdo->prepare("INSERT INTO login_attempts (ip) VALUES (:ip)");
            $stmt->execute([':ip' => $ip]);

            http_response_code(401);
            echo json_encode(['error' => true, 'message' => 'Identifiants incorrects']);
            return;
        }

        // Connexion réussie — supprimer les tentatives de cette IP
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE ip = :ip");
        $stmt->execute([':ip' => $ip]);

        Auth::startSession();
        session_regenerate_id(true);

        $_SESSION['user_id']  = $found['id'];
        $_SESSION['username'] = $found['username'];
        $_SESSION['role']     = $found['role'];
        $_SESSION['magasin']  = $found['magasin'];

        http_response_code(200);
        echo json_encode([
            'error'    => false,
            'role'     => $found['role'],
            'magasin'  => $found['magasin'],
            'username' => $found['username'],
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Erreur côté serveur']);
    }
}

    /**
     * Détruit la session active.
     * Appelé par api/logout.php
     */
    public function logout(): void {

        Auth::startSession();
        session_destroy();

        http_response_code(200); 
        echo json_encode(['error' => false, 'message' => 'Déconnecté']);
    }


    /**
     * Lit $_SESSION et retourne les infos du connecté 
     * Utilisé par Vue au démarage pour savoir si une session existe déja
     * Appelé par api/check-auth.php
     */
    public function checkAuth(): void {

        Auth::startSession();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => true, 'message' => 'Non authentifié']);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'error'    => false,
            'role'     =>$_SESSION['role'],
            'magasin'  =>$_SESSION['magasin'],
            'username' =>$_SESSION['username'],
        ]);
    }
}