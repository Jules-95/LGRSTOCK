<?php 
/**
 * Endpoint : Verification de session
 * Recoit   : GET (cookie de session)
 * Appelle  : AuthController::checkAuth()
 */
require_once __DIR__ . '/../../config/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

$pdo = getDBConnection();
$controller = new AuthController($pdo);
$controller->checkAuth();