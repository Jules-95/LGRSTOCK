<?php
/**
 * API : Ajouter un produit
 * Utilisation : POST api/add-product.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Cet endpoint n'accepte que les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 = Method Not Allowed
    echo json_encode(['error' => true, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';

$pdo = getDBConnection();

$controller = new ProductController($pdo);

$controller->addProduct();