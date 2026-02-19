<?php 

/** 
 * API de récupération d'un produit par son ID
 * 
 * Paramètre obligatoire : 
 * - id : ID du produit à récupérer
 * 
 * Exemple : 
 * GET /api/product.php?id=5
 */

// En-tête HTTP pour l'API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Inclure la connexion à la BDD
require_once '../config/database.php'; 

try {
    // Obtenir la connexion 
    $pdo = getDBConnection();

    // Récupérer l'ID depuis l'URL
    $productId = isset($_GET['id']) ? trim ($_GET['id']) : null;

    // verifier que l'Id est fourni
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'L\'ID du produit est obligatoire'
        ]);
        exit;
    }

    // Verifier que l'ID est un nombre
    if (!is_numeric($productId)) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'L\'ID du produit doit être un nombre',
            'details' => 'ID fourni : "' . $productId . '"'
        ]);
        exit;
    }

    // Requête SQL pour récupérer le produit 
    $sql = "SELECT * FROM products WHERE id = :id LIMIT 1";

    // Préparer et exécuter la requête 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $productId]);

    //Récupérer le résultat 
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si le produit existe
    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Produit non trouvé',
            'details' => 'Aucun produit avec l\'ID' . $productId
        ]);
        exit;
    }

    // Renvoyer le produit 
    http_response_code(200);
    echo json_encode([
        'error' => false,
        'data' => $product
    ]);

} catch (PDOException $e) {
    // Erreur de base de donnée
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erreur lors de la récupération du produit',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Autre erreur
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Une erreur est survenue',
        'details' => $e->getMessage()
    ]);
}
?>