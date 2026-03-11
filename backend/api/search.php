<?php
/**
 * API : Recherche de produits
 * 
 * Utilisation : GET /api/search.php?ean=...&libelle=...&fournisseur=...
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';

$pdo = getDBConnection(); 

$controller = new ProductController($pdo);

$controller->search();