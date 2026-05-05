<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../src/Middleware/Auth.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/UserController.php';

$pdo = getDBConnection();
$controller = new UserController($pdo);
$controller->addUser();