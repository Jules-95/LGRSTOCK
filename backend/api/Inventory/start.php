<?php
/**
 * API : Démarrer un inventaire (remise à 0 du stock du dépôt de la session)
 * Utilisation : POST /Inventory/start.php
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

$controller->demarrer();
