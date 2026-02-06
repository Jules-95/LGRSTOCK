<?php 
/**
 * API de recherche de produits
 * 
 * Paramètres acceptés : 
 * - ean : Recherche par code EAN (exact)
 * - libelle : Recherche par libellé (partiel, insensibleà la casse)
 * - fournisseur : Recherche par fournisseur (partiel)
 * 
 * Exemple : 
 * GET /api/search.php?ean=3700523456789
 * GET /api/search.php?libelle=lego
 */

// En-têtes HTTP pour l'API
header('Content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Permet les requ^tes depuis le frontend

// Inclure la connexion à la base de données 
require_once '../config/database.php';

try {
    // Obtenir la connexion
    $pdo = getDBConnection();

    // Récupérer les paramètres de recherche depuis l'URL
    $ean = isset($_GET['ean']) ? trim($_GET['ean']) : null;
    $libelle = isset($_GET['libelle']) ? trim($_GET['libelle']) : null;
    $fournisseur = isset($_GET['fournisseur']) ? trim($_GET['fournisseur']) : null;

    // Vérifier qu'au moins un critère est fourni 
    if (empty($ean) && empty($libelle) && empty($fournisseur)) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => true,
            'message' => 'Veuillez fournir au moins un critère de recherche (ean, libelle ou fournisseur)'
        ]);
        exit;
    }

    // Construire la requête SQL selon les critères
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($ean) {
        // Recherche exacte par EAN
        $sql .= " AND ean = :ean";
        $params[':ean'] = $ean;
    }

    if ($libelle) {
        // Recherche partielle par libelle
        $sql .= " AND libelle LIKE :libelle";
        $params[':libelle'] = '%' . $libelle . '%';
    }

    if ($fournisseur) {
        // Recherche partielle par fournisseur
        $sql .= " AND fournisseur LIKE :fournisseur";
        $params[':fournisseur'] = '%' . $fournisseur . '%';
    }

    // Limiter les résultats à 50 maximum
    $sql .= " LIMIT 50";

    // Préparer et exécuter la requête 
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Récupérer tous les résultats 
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Renvoyer la réponse JSON
    http_response_code(200); // OK
    echo json_encode([
        'error' => false,
        'count' => count($products),
        'data' => $products
    ]);

} catch (PDOException $e) {
    // Erreur de BDD
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => true,
        'message' => 'Erreur lors de la recherche',
        'details' => $e->getMessage()
    ]);

} catch (Exception $e) {
    // Autre erreur
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'une erreur est survenue',
        'details' => $e->getMessage()
    ]);
}
?>