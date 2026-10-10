<?php
/**
 * API : Terminer l'inventaire en cours du dépôt de la session
 * Utilisation : POST /Inventory/finish.php
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../src/Middleware/Auth.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Controllers/InventoryController.php';

$pdo = getDBConnection();

$controller = new InventoryController($pdo);

$controller->terminer();
