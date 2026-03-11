<?php 
/**
 * API : Récupération d'un produit par ID
 * 
 * Utilisation : GET /api/product.php?id=42
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';

$pdo = getDBConnection();

//Instancier le Controller
$controller = new ProductController($pdo);

//Appeler la méthode appropriée
$controller->getById();