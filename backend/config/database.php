<?php
// Les credentials sont lus depuis .env (jamais commité sur GitHub)

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    error_log("Fichier .env introuvable");
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Configuration serveur manquante']);
    exit;
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    [$key, $value] = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
}

// Affichage des erreurs PHP uniquement en développement
ini_set('display_errors', $_ENV['APP_ENV'] === 'production' ? 0 : 1);
error_reporting(E_ALL);

define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? '');
define('DB_USER',    $_ENV['DB_USER']    ?? '');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Erreur BDD : " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Erreur de connexion à la base de données']);
        exit;
    }
}