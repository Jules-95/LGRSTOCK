<?php
/**
 * API : Statistiques globales du dashboard admin
 * 
 * Utilisation : GET /api/stats.php
 * 
 * Pas de Model dédié : pas une entité métier avec du CRUD. La logique SQL reste dans le Controller.
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../src/Middleware/Auth.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/StatsController.php';

$pdo = getDBConnection();

$controller = new StatsController($pdo);

$controller->getStats();