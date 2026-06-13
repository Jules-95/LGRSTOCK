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
     * @param string $depot 'tours_nord' ou 'tours_centre' (magasin de l'admin connecté)
     * @return array ['id' => int, 'action' => 'created'|'updated', 'libelle' => string, 'depot' => string, 'quantite' => int]
     * @throws Exception si données invalides
     */
    public function create($data, $depot) {
    $libelle         = $data['libelle']         ?? null;
    $ean             = $data['ean']             ?? null;
    $fournisseur     = $data['fournisseur']     ?? null;
    $quantite        = $data['quantite']        ?? 0;
    $prix            = $data['prix']            ?? null;
    $ref_fournisseur = $data['ref_fournisseur'] ?? null;

    if (empty($libelle)) {
        throw new Exception("Le libellé est obligatoire");
    }
    if (empty($ean)) {
        throw new Exception("Le code EAN est obligatoire");
    }
    $this->validateEan($ean);
    if (!is_numeric($quantite) || $quantite < 0) {
        throw new Exception("La quantité doit être un nombre positif ou nul");
    }
    if (!in_array($depot, ['tours_nord', 'tours_centre'], true)) {
        throw new Exception("Dépot invalide");
    }

    $autreDepot = $depot === 'tours_nord' ? 'tours_centre' : 'tours_nord';

    try {
        $this->pdo->beginTransaction();

        // Le produit existe-t-il déjà (par son EAN) ?
        $stmt = $this->pdo->prepare("SELECT id, libelle FROM products WHERE ean = :ean");
        $stmt->execute([':ean' => $ean]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // CAS A : EAN existant → on ne recrée pas, on fixe la quantité du dépôt
            $productId   = (int) $existing['id'];
            $libelleReel = $existing['libelle'];   // le vrai libellé en base
            $action      = 'updated';

            $sqlDepot = "INSERT INTO product_depot (product_id, depot, quantite)
                         VALUES (:product_id, :depot, :quantite)
                         ON DUPLICATE KEY UPDATE quantite = :quantite_update";
            $stmtDepot = $this->pdo->prepare($sqlDepot);
            $stmtDepot->execute([
                ':product_id'      => $productId,
                ':depot'           => $depot,
                ':quantite'        => (int) $quantite,
                ':quantite_update' => (int) $quantite,
            ]);

        } else {
            // CAS B : nouveau produit → création + 2 lignes dépôt
            $libelleReel = $libelle;
            $action      = 'created';

            $sql = "INSERT INTO products (libelle, ean, fournisseur, quantite, ref_fournisseur, prix, created_at, updated_at)
                    VALUES (:libelle, :ean, :fournisseur, :quantite, :ref_fournisseur, :prix, NOW(), NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':libelle'         => $libelle,
                ':ean'             => $ean,
                ':fournisseur'     => $fournisseur,
                ':quantite'        => (int) $quantite,
                ':ref_fournisseur' => $ref_fournisseur,
                ':prix'            => $prix !== null && $prix !== '' ? (float) $prix : null,
            ]);
            $productId = (int) $this->pdo->lastInsertId();

            // Ligne du dépôt de l'admin (avec la quantité)
            $stmtDepot = $this->pdo->prepare(
                "INSERT INTO product_depot (product_id, depot, quantite) VALUES (:product_id, :depot, :quantite)"
            );
            $stmtDepot->execute([
                ':product_id' => $productId,
                ':depot'      => $depot,
                ':quantite'   => (int) $quantite,
            ]);

            // Ligne de l'autre dépôt (à 0)
            $stmtAutre = $this->pdo->prepare(
                "INSERT INTO product_depot (product_id, depot, quantite) VALUES (:product_id, :depot, 0)"
            );
            $stmtAutre->execute([
                ':product_id' => $productId,
                ':depot'      => $autreDepot,
            ]);
        }

        // Recalcul du total (somme des 2 dépôts)
        $sqlTotal = "UPDATE products
                     SET quantite = (SELECT SUM(quantite) FROM product_depot WHERE product_id = :pid)
                     WHERE id = :id";
        $stmtTotal = $this->pdo->prepare($sqlTotal);
        $stmtTotal->execute([':pid' => $productId, ':id' => $productId]);

        $this->pdo->commit();

        // On renvoie ce qui s'est passé, pour le message côté Vue
        return [
            'id'       => $productId,
            'action'   => $action,        // 'created' ou 'updated'
            'libelle'  => $libelleReel,
            'depot'    => $depot,
            'quantite' => (int) $quantite,
        ];

    } catch (PDOException $e) {
        $this->pdo->rollBack();
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
    public function update($id, $data, $depot) {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception("L'ID du produit doit être valide");
        }

        $libelle         = $data['libelle']                    ?? null;
        $ean             = $data['ean']                        ?? null;
        $fournisseur     = $data['fournisseur']                ?? null;
        $quantite        = $data['quantite']                   ?? null;
        $ref_fournisseur = $data['ref_fournisseur'] ?? null;
        $prix            = $data['prix']                       ?? null;

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

        //Check de l'existance du produit 
        $check = $this->pdo->prepare("SELECT id FROM products WHERE id = :id");
        $check->execute([':id' => (int) $id]);
        if (!$check->fetch()) {
            throw new Exception("Produit introuvable");
        }

        // validation du dépot
        if (!in_array($depot, ['tours_nord', 'tours_centre'], true)) {
            throw new Exception("Dépot invalide");
        }



        $sql = "UPDATE products SET 
            libelle = :libelle,
            ean = :ean,
            fournisseur = :fournisseur, 
            ref_fournisseur = :ref_fournisseur,
            prix = :prix,
            updated_at = NOW() 
            WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        try {
        $stmt->execute([
            ':libelle' => $libelle,
            ':ean' => $ean,
            ':fournisseur' => $fournisseur,
            ':ref_fournisseur' => $ref_fournisseur,
            ':prix' => $prix !== null && $prix !== '' ? (float) $prix : null,
            ':id' => (int) $id
        ]);

        // Mise à jour ou création de la ligne dépot
        $sqlDepot = "INSERT INTO product_depot(product_id, depot, quantite)
        VALUES (:product_id, :depot, :quantite)
        ON DUPLICATE KEY UPDATE quantite = :quantite_update";
        $stmtDepot = $this->pdo->prepare($sqlDepot);
        $stmtDepot->execute([
            ':product_id' => (int) $id,
            ':depot'      => $depot,
            ':quantite'   => (int) $quantite,
            ':quantite_update' => (int) $quantite,
        ]);

        // Calcul du total (somme des dépots)
        $sqlTotal = "UPDATE products
        SET quantite = (SELECT SUM(quantite) FROM product_depot WHERE product_id = :pid)
        WHERE id = :id";
        $stmtTotal = $this->pdo->prepare($sqlTotal);
        $stmtTotal->execute([
            ':pid' => (int) $id,
            ':id'  => (int) $id,
        ]);




        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new Exception("Ce code EAN est déja utilisé pour un autre produit");
            }
            throw new Exception("Erreur technique : " . $e->getMessage());
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

        // Tri des résulats de la recherche
        $sql .= " ORDER BY libelle ASC"; 

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