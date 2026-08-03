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
                'details' => 'Une erreur est survenue'
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

            // Récupérer la page demandée (Défaut : 1)
            $page = isset($_GET['page']) && is_numeric($_GET['page'])
            ? (int) $_GET['page']
            : 1;

            $result = $this->productModel->search($filters, $page);


            //Renvoyer les résultats 
            $this->sendResponse(200, [
                'error' => false,
                'count' => count($result['data']),
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit']
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
            $prix = $_POST['prix'] ?? null;
            $ref_fournisseur = $_POST['ref_fournisseur'] ?? null;

            $depot = $_SESSION['magasin'] ?? null;

            // On passe toutes les données au Model dans un tableau
            $result = $this->productModel->create([
                'libelle' => $libelle,
                'ean' => $ean,
                'fournisseur' => $fournisseur,
                'quantite' => $quantite,
                'prix' => $prix,
                'ref_fournisseur' => $ref_fournisseur
            ], 
            $depot);

            $this->sendResponse(200, [
                'error' => false,
                'action' => $result['action'],
                'id' => $result['id'],
                'libelle' => $result['libelle'],
                'depot' => $result['depot'],
                'quantite' => $result['quantite'],
            ]);

        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }


    /**
     * supprime un produit 
     * Utilisé par : POST /api/delete-product.php
     */
    public function deleteProduct() {
        try {
            $id = $_POST['id'] ?? null;

            $this->productModel->delete($id);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Produit supprimé avec succès' 
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }





    /**
     * Modification d'un produit 
     * Utilisé par : POST /api/edit-product.php
     */
    public function editProduct() {
         try {
            $id = $_POST['id'] ?? null;
            $depot = $_SESSION['magasin'] ?? null;

            $this->productModel->update($id, [
                'libelle'         => $_POST['libelle'] ?? null,
                'ean'             => $_POST['ean'] ?? null,
                'fournisseur'     => $_POST['fournisseur'] ?? null,
                'quantite'        => $_POST['quantite'] ?? 0,
                'prix'            => $_POST['prix'] ?? null,
                'ref_fournisseur' => $_POST['ref_fournisseur'] ?? null,
            ],
            $depot);

            // On relit le produit à jour (avec qte_nord, qte_centre et le total
            // recalculé) pour que le frontend affiche l'état réel sans deviner.
            $product = $this->productModel->findById($id);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Produit mit à jour avec succès',
                'data' => $product
            ]);
        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ajuster le stock d'un dépôt (Ajout ou retrait ponctuel)
     * Utilisé par POST/api/Product/adjust-stock.php
     */
    public function ajusterStock() {
        try {
            $id    = $_POST['id']    ?? null;
            $delta = $_POST['delta'] ?? null;
           

            // Depot provient de la session, un admin ne peut gérer que son dépot.
            $depot = $_SESSION['magasin'] ?? null; 

            if (!is_numeric($delta) || (int) $delta === 0) {
                throw new Exception("La variation doit être un nombre non nul");
            }

            $product = $this->productModel->ajusterStock($id, $depot, (int) $delta);

            $this->sendResponse(200, [
                'error' => false,
                'message' => 'Stock ajusté avec succès',
                'data' => $product,
            ]);

        } catch (Exception $e) {
            $this->sendResponse(400, [
                'error' => true,
                'message' => $e->getMessage(),
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

        // Envoyer le JSON 
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        //Arrêter l'exécution
        exit;
    }
}
