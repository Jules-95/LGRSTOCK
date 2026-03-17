<?php

/**
 * Controller qui gère les requêtes HTTP liées aux produits :
 * - Récupère les données de la requête ($_GET, $_POST)
 * - Valide les données HTTP
 * - Appelle le Model Product.php
 * - Formate et renvoie les réponses Json
 */

require_once __DIR__ . '/../Models/Product.php';

class ProductController
{
    private $productModel;


    /**
     * Constructeur 
     * @param PDO $pdo Connexion à la BDD
     */

    public function __construct($pdo)
    {
        // Instancier le Model
        $this->productModel = new Product($pdo);
    }


    // 1. METHODES PUBLIQUES (Une par endpoint API)

    public function getById()
    {
        try {
            //a. Récup l'id depuis l'url 
            $id = $_GET['id'] ?? null;
            //b. Appeler le MODEL (qui fait la validation)
            $product = $this->productModel->findById($id);
            //c. Vérifier si le produit existe
            if (!$product) {
                $this->sendResponse(404, [
                    'error' => true,
                    'message' => 'Produit non trouvé',
                    'details' => "Aucun produit avec l'ID $id"
                ]);
                return;
            }

            //d. Renvoyer le produit
            $this->sendResponse(200, [
                'error' => false,
                'data' => $product
            ]);
        } catch (Exception $e) {
            // Erreur (Validation ou autre)
            $this->sendResponse(400, [
                'error' => true,
                'message' => 'Erreur lors de la récupération du produit',
                'details' => $e->getMessage()
            ]);
        }
    }


    /**
     * Recherche de produits selon critères
     * Utilisé par : GET /api/search.php?ean=...&libelle=...&fournissuer=..
     * 
     * @return void
     */
    public function search()
    {
        try {
            //a. Récup les critères depuis l'url
            $filters = [
                'ean' => $_GET['ean'] ?? null,
                'libelle' => $_GET['libelle'] ?? null,
                'fournisseur' => $_GET['fournisseur'] ?? null
            ];

            //b. Appeler le Model (Qui fait la validation) 
            $products = $this->productModel->search($filters);

            //c. Renvoyer les résultats 
            $this->sendResponse(200, [
                'error' => false,
                'count' => count($products),
                'data' => $products
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage(),
                'details' => 'Erreur lors de la recherche'
            ]);
        }
    }


    /**
     * Ajouter un nouveau produit
     * Utilisé par : POST / api/add-product.php
     */
    public function addProduct() {
        try {
            // On lit les données envoyées en POST comme pour updateStock
            $libelle = $_POST['libelle'] ?? null;
            $ean = $_POST['ean'] ?? null;
            $fournisseur = $_POST['fournisseur'] ?? null;
            $quantite = $_POST['quantite'] ?? 0;

            // On passe toutes les données au Model dans un tableau
            $newId = $this->productModel->create([
                'libelle' => $libelle,
                'ean' => $ean,
                'fournisseur' => $fournisseur,
                'quantite' => $quantite
            ]);

            //Renvoyer l'ID du nouveau produit 
            //Vue pourra rediriger vers /product/$newId
            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Produit ajouté avec succès',
                'id' => $newId
            ]);

        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Mise à jour de la quantite d'un produit 
     * Utilisé par : POST /api/update-stock.php
     */
    public function updateStock()
    {
        try {
            // On va lire le $_POST au lieu du JSON brut (plus simple + debug)
            $id       = $_POST['id']        ?? null;
            $quantite = $_POST['quantite'] ?? null;

            $this->productModel->updateQuantite($id, $quantite);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Quantité mise à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    // 2. METHODES PRIVATE (utilitaires)

    /**
     * Envoie une réponse JSON avec le bon code HTTP
     * 
     * @param int $statusCode Code HTTP (200, 400, 404, 500...)
     * @param array $data Données a renvoyer en JSON
     * @return void
     */
    private function sendResponse($statusCode, $data)
    {
        // Definir le code HTTP 
        http_response_code($statusCode);

        // Définir les headers (Métadonnées -> Dit au navigateur :)
        header('Content-Type: application/json; charset=utf-8'); // "J'envoie du JSON en UTF-8
        header('Access-Control-Allow-Origin: *'); // "Le front (localhost:5173) a le droit de m'appeler 

        // Envoyer le JSON 
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        //Arrêter l'exécution
        exit;
    }
}
