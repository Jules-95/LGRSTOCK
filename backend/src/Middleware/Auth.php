<?php 

/**
 * ============================================================
 * MIDDLEWARE D'AUTHENTIFICATION
 * ============================================================
 * 
 * Un middleware est une logique transversale exécutée AVANT le traitement principal d'un requete. 
 * 
 * CE QU'IL RECOIT:
 * - La session PHP active (cookie PHPSESSID envoyé par le navigateur)
 * - $_SESSION['user_id], $_SESSION['role'] définis au login
 * 
 * CE QU'IL FAIT : 
 * - requireAuth() -> Vérifie qu'il y a une session active 
 * - requireAdmin() -> Vérifie qu'il y a une session active ET role = 'admin'
 * - Si la vérification échoue -> Renvoie d'une erreur JSON et stop
 * - Si elle reussit -> Rien, le script continue 
 * 
 * CE QU'IL DESSERT (Qui l'appelle) : 
 * - api/login.php          -> pour démarrer la session 
 * - api/logout.php         -> Pour détruire la session
 * - api/check-auth         -> Pour lire la session 
 * - api/add-product        -> Protégé par requireAdmin()
 * - api/update-stock.php   -> Protégé par requireAdmin()
 * - api/delete-product.php -> Protégé par requireAdmin()
 * 
 * RESUME : 
 * Reçoit -> cookie de session 
 * Traite -> Verifie si la session est valide et le role suffisant
 * produit -> Laisser passer OU renvoie une erreur 400...
 * ============================================================
 */

class Auth {
    /**
     * Vérifie qu'une session active existe 
     * Utilisé pour les actions accessibles à tous les connectés.
     */
    public static function requireAuth() : void {
        self::startSession();

        if (!isset($_SESSION ['user_id'])) {
            self::deny(401, 'Non authentifié');
        }
    }

    /**
     * Vérifie qu'une session active existe Et que le role est admin. 
     * Utilisé pour les actions de gestion (CRUD, import CSV )
     */
    public static function requireAdmin() : void {
        self::startSession(); 

        if (!isset($_SESSION['user_id'])) {
            self::deny(401, 'Non authentifié');
        }

        if ($_SESSION['role'] !== 'admin') {
            self::deny(403, 'Accès refusé'); 
        }
    }


    /**
     * Démarre la session si elle n'est pas déjà active.
     */
    public static function startSession() : void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 28800); // Durée des données coté serveur (8h)
            session_set_cookie_params([
                'lifetime' => 28800, // Durée du cookie coté navigateur (8h)
                'httponly' => true, // Inaccessible au Javascript
                'samesite' => 'Strict',
                'secure'   => isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production' 
            ]);
            session_start();
        }
    }

    /**
     * Stoppe l'exécution et renvoie une erreur JSON
     * exit() est indispensable - 
     * Sans, le script continuerait à s'exécuter après l'erreur.
     */
    private static function deny(int $code, string $message): void {
        http_response_code($code); 
        echo json_encode([
            'error' => true,
            'message' => $message,
        ]);
        exit();
    }
}


