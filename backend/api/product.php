<?php 
/**
 * API : Récupération d'un produit par ID
 * 
 * Utilisation : GET /api/product.php?id=42
 */

// Cet endpoint n'accepte que les requêtes GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // 405 = Method Not Allowed
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';

$pdo = getDBConnection();

//Instancier le Controller
$controller = new ProductController($pdo);

//Appeler la méthode appropriée
$controller->getById();