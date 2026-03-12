<?php 

class Product {
    // 1. PROPRIETES
    private $pdo;

    // 2. CONSTRUCTEUR
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 3. METHODES PUBLIQUES 

    /**
     * Recherche d'un produit par son ID
     * @param int $id
     * @return array|null Le produit trouvé ou null
     * @throws Exception si ID invalide
     */
    public function findById($id) {
        // Validation de l'ID (sécurité) 
        if (empty($id) || !is_numeric($id)) {
            throw new Exception("L'ID du produit doit etre valide");
        }

        $sql = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }


    /**
     * Recherche d'un produit selon critères
     * @param array $filters ['ean' => '', 'libelle' => '', 'fournisseur' => '']
     * @return array Tableau de produit (Peut etre vide)
     */
    public function search($filters) {
        $ean = $filters['ean'] ?? null;
        $libelle = $filters['libelle'] ?? null;
        $fournisseur = $filters['fournisseur'] ?? null;

        // Vérification qu'au moin un critère soit fourni 
        if (empty($ean) && empty($libelle) && empty($fournisseur)) {
            throw new Exception("Veuillez fournir au moins un critère de recherche");
        }

        // Si recherche par ean -> Validation 
        if ($ean) {
            $this->validateEan($ean);
        }

        //Construction de la reqête SQL dynamique (1=1 -> Astuce pour faciliter l'ajout de conditions dynamiques)
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];
        // Ajouter les conditions selon les critères fournis
        if ($ean) {
            $sql .= " AND ean = :ean";
            $params[':ean'] = $ean;
        }

        if ($libelle) {
            $escaped = addcslashes($libelle, '%_');
            $sql .= " AND libelle LIKE :libelle";
            $params[':libelle'] = '%' . $escaped . '%';
        }

        if ($fournisseur) {
            $escaped = addcslashes($fournisseur, '%_');
            $sql .= " AND fournisseur LIKE :fournisseur";
            $params[':fournisseur'] = '%' . $escaped . '%';
        }

        // Limiter le nombre de résultat 
        $sql .= " LIMIT 20";

        //Requête préparée
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }





    // 4. METHODES PRIVATE (utilitaires internes)

    /**
     * Validité d'un code EAN 
     * @param string $ean Le code ean à Valider
     * @return bool true si valide
     * @throws Exception si EAN invalide
     */
    private function validateEan($ean) {
        if (strlen($ean) !== 13) {
            throw new Exception("Le code EAN doit contenir exactement 13 chiffres");
        }

        if (!ctype_digit($ean)) {
            throw new Exception("Le code EAN ne doit contenir que des chiffres (0-9)");
        }

        return true;
    }

}