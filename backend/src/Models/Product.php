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
     * Ajouter un nouveau produit en BDD
     * @param array $data ['libelle' => '', 'ean' => '', 'fournisseur' => '', 'quantite' => 0]
     * @return int L'ID du produit crée
     * @throws Exception si données invalides
     */
    public function create($data) {
        $libelle     = $data['libelle']     ?? null;
        $ean         = $data['ean']         ?? null;
        $fournisseur = $data['fournisseur'] ?? null;
        $quantite    = $data['quantite']    ?? 0;

        // Validation : Champs obligatoires 
        if (empty($libelle)) {
            throw new Exception("Le libellé est obligatoire");
        }

        if (empty($ean)) {
            throw new Exception("Le code EAN est obligatoire");
        }

        // Réutilisation de la validation EAN 
        $this->validateEan($ean);

        // Double Validation front/back pour la valeur et et le type de "quantite"
        if (!is_numeric($quantite) || $quantite < 0) {
            throw new Exception("La quantité doit être un nombre positif ou nul");
        }

        try {
        // Requête préparée INSERT 
        // NOW() remplit automatiuement created_at et updated_at

        $sql = "INSERT INTO products (libelle, ean, fournisseur, quantite, created_at, updated_at) VALUES (:libelle, :ean, :fournisseur, :quantite, NOW(), NOW())";

        $stmt= $this->pdo->prepare($sql);
        $stmt->execute([
            ':libelle'     => $libelle,
            ':ean'         => $ean,
            ':fournisseur' => $fournisseur,
            ':quantite'    => (int) $quantite
        ]);

        // On va retourner l'ID généré par MySQL pour la ligne qu'on vient d'insérer. (Pour afficher la nouvelle fiche détaillée en cas de produit ajouté)
        return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            // Code 23000 = Violation de contrainte unique qui se déclenche quand un code EAN existe déjà
            if ($e->getCode() === '23000') {
                // Recherche du libelle du produit qui existe pour l'afficher dans le message d'erreur
                $stmt = $this->pdo->prepare("SELECT libelle FROM products WHERE ean= :ean");
                $stmt->execute([':ean' => $ean]);
                $productAlreadyExist = $stmt->fetch(PDO::FETCH_ASSOC);

                $existProduct = $productAlreadyExist ? $productAlreadyExist['libelle'] : 'inconnu';

                throw new Exception("Référence EAN déjà présente en stock / Produit : " . $existProduct);
            } 

            // Si c'est une autre erreur PDO, on la remonte telle quelle 
            throw new Exception("Erreur technique : " . $e->getMessage());
        }
    }


    /**
     * Supprimer un produit 
     * @param int $id
     * @return bool true si suppression réussi
     * @throws Exception si ID invalide ou produit introuvable 
     */
    public function delete($id) {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception("L'ID du produit doit etre valide");
        }

        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int) $id]);

        // si aucune ligne supprimée, l'ID n'existait pas 
        if ($stmt->rowCount() === 0) {
            throw new Exception("Produit introuvable");
        }

        return true;
    }


    /**
     * Mise à jour de la quantité d'un produit
     * @param int $id
     * @param array $data ['libelle', 'ean', 'fournisseur', 'quantite']
     * @return bool true si la mise à jour a réussi
     * @throws Exception si données invalides
     */
    public function update($id, $data) {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception("L'ID du produit doit être valide");
        }

        $libelle     = $data['libelle']     ?? null;
        $ean         = $data['ean']         ?? null;
        $fournisseur = $data['fournisseur'] ?? null;
        $quantite    = $data['quantite']    ?? null;

        if (empty($libelle)) {
            throw new Exception("Le libellé est obligatoire");
        }

        if (empty($ean)) {
            throw new Exception("Le code EAN est obligatoire");
        }

        if (strlen($ean) !== 13 || !ctype_digit($ean)) {
            throw new Exception("Le code EAN doit contenir exactement 13 chiffres");
        }

        if (!is_numeric($quantite) || $quantite < 0) {
            throw new Exception("La quantité doit être un nombre positif ou nul");
        }

        $sql = "UPDATE products SET 
            libelle = :libelle,
            ean = :ean,
            fournisseur = :fournisseur,
            quantite = :quantite, 
            updated_at = NOW() 
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
        $stmt->execute([
            ':libelle' => $libelle,
            ':ean' => $ean,
            ':fournisseur' => $fournisseur,
            ':quantite' => (int) $quantite,
            ':id' => (int) $id
        ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new Exception("Ce code EAN est déja utilisé pour un autre produit");
            }
        }

        //rowCountretourne le nombre de lignes affectées par l'UPDATE
        // Si 0 : l'ID n'existe pas en BDD
        if ($stmt->rowCount() === 0) {
            throw new Exception("Produit introuvable");
        }

        return true;
    }


    /**
     * Recherche d'un produit selon critères
     * @param array $filters ['ean' => '', 'libelle' => '', 'fournisseur' => '']
     * @return array Tableau de produit (Peut etre vide)
     */
    public function search($filters, $page = 1, $limit = 20) {
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

        // Compter le total avant LIMIT/OFFSET
        $sqlCount = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Ajout de LIMIT et OFFSET
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        // Retourner le tableau structuré 
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
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