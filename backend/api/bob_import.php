<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Middleware/Auth.php';
require_once __DIR__ . '/../src/Controllers/BobImportController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

Auth::requireAdmin();

$pdo = getDBConnection();
$controller = new BobImportController($pdo);
$controller->import();