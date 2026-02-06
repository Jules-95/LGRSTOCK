<?php 
// Configuration de la connexion à la base de données

// Constantes de connexion 
define('DB_HOST', 'localhost');     // Serveur MySQL
define('DB_NAME', 'lgr_stock');     // Nom de la base
define('DB_USER', 'root');          // Utilisateur par défaut "root" sur XAMPP
define('DB_PASS', '');              // Mdp (vide par défaut sur XAMPP)
define('DB_CHARSET', 'utf8mb4');    // Encodage des caractères

/**
 * Fonction pour obtenir une connexion à la bdd
 * @return PDO Instance de connexion PDO
 * @throws PDOException Si la connexion échoue
 */

function getDBConnection() {
    try {
        // DSN (Data Source Name) = chaine de connexion
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        // Option PDO pour améliorer la sécurité et le comportement
        $options = [
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,  // Affiche les erreurs SQL
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC, // Résultats en tableau associatif
            PDO::ATTR_EMULATE_PREPARES      => false // Vraies requêtes préparées
        ];

        //Créer la connexion PDO
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En cas d'erreur de connexion
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
?>