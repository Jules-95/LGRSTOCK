<?php
// Inclure le fichier de configuartion
require_once 'config/database.php';

echo "<h1>Test de connexion à la BDD</h1>";

try {
    // obtenir la connexion
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Connexion réussie !</p>";

    // test d'une requête simple
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $result = $stmt->fetch();

    echo "<p>Nombre de produits dans la base : <strong>" . $result['total'] . "</strong></p>";

    // Test d'affichage du nom des produits
    $stmt = $pdo->query("SELECT libelle FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<ul>";
    foreach ($products as $product) {
        echo "<li><strong>" . htmlspecialchars($product['libelle']) . "</strong></li>";
    }
    echo "</ul>";


} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>