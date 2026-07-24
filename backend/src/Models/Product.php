<?php 

class Product {
    // 1. PROPRIETES
    private $pdo;

    // Liste de référence des dépots pour éviter les chaines en dur éparpillées
    private const DEPOTS = ['tours_nord', 'tours_centre']; 

    // 2. CONSTRUCTEUR
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 3. METHODES PUBLIQUES 

    /**
     * Recherche d'un produit par son ID
     * @param int $id
     * @return array|null Le produit (qté Nord et qté Centre) ou null
     * @throws Exception si ID invalide
     */
    public function findById($id) {
        // Validation de l'ID (sécurité) 
        if (empty($id) || !is_numeric($id)) {
            throw new Exception("L'ID du produit doit etre valide");
        }

    $sql = "SELECT products.*,
            (SELECT quantite FROM product_depot WHERE product_id = products.id AND depot = 'tours_nord')   AS qte_nord,
            (SELECT quantite FROM product_depot WHERE product_id = products.id AND depot = 'tours_centre') AS qte_centre
            FROM products WHERE id = :id LIMIT 1";
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
    $this->validateDepot($depot);

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

            $this->ajusterDepotQuantite($productId, $depot, $quantite);

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

           $this->setDepotQuantite($productId, $depot, $quantite);
           $this->setDepotQuantite($productId, $autreDepot, 0);
        }

        // Recalcul du total (somme des 2 dépôts)
       $this->recalculerTotal($productId);

        $this->pdo->commit();

        // On renvoie ce qui s'est passé, pour le message côté Vue
        return [
            'id'       => $productId,
            'action'   => $action,        // 'created' ou 'updated'
            'libelle'  => $libelleReel,
            'depot'    => $depot,
            'quantite' => $this->getDepotQuantite($productId, $depot),
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
        $this->validateDepot($depot);



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

        $this->setDepotQuantite($id, $depot, $quantite);
        $this->recalculerTotal($id);




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
 * @return array Tableau de produits avec qte_nord et qte_centre (peut être vide)
 */
public function search($filters, $page = 1, $limit = 20) {
    $ean = $filters['ean'] ?? null;
    $libelle = $filters['libelle'] ?? null;
    $fournisseur = $filters['fournisseur'] ?? null;

    // Au moins un critère requis
    if (empty($ean) && empty($libelle) && empty($fournisseur)) {
        throw new Exception("Veuillez fournir au moins un critère de recherche");
    }

    if ($ean) {
        $this->validateEan($ean);
    }

    //  Pour chaque produit, sa quantité dans chaque dépôt
    $sql = "SELECT products.*,
            COALESCE(pd_nord.quantite, 0)   AS qte_nord,
            COALESCE(pd_centre.quantite, 0) AS qte_centre
            FROM products
            LEFT JOIN product_depot AS pd_nord
                   ON pd_nord.product_id = products.id AND pd_nord.depot = 'tours_nord'
            LEFT JOIN product_depot AS pd_centre
                   ON pd_centre.product_id = products.id AND pd_centre.depot = 'tours_centre'
            WHERE 1=1";
            
    $params = [];

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

    $sql .= " ORDER BY libelle ASC";

    // COUNT séparé
    $sqlCount = "SELECT COUNT(*) FROM products WHERE 1=1";
    if ($ean)         $sqlCount .= " AND ean = :ean";
    if ($libelle)     $sqlCount .= " AND libelle LIKE :libelle";
    if ($fournisseur) $sqlCount .= " AND fournisseur LIKE :fournisseur";

    $stmtCount = $this->pdo->prepare($sqlCount);
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();

    // Pagination
    $offset = ($page - 1) * $limit;
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'data'  => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total' => $total,
        'page'  => $page,
        'limit' => $limit
    ];
}




    // 4. METHODES PRIVATE (utilitaires internes)

    /**
     * Valide qu'un dépôt fait partie des dépôts connus.
     * @throws Exception si le dépôt est invalide
     */
    private function validateDepot($depot) {
        if (!in_array($depot, self::DEPOTS, true)) {
            throw new Exception("Dépôt invalide");
        }
    }

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

    /**
     * Fixer (poser) la quantité absolue d'un produit dans un dépôt.
     * Crée la ligne si elle n'existe pas, la remplace sinon.
     * Garantit de ne jamais descendre sous 0.
     *
     * @param int $productId
     * @param string $depot 'tours_nord' ou 'tours_centre'
     * @param int $quantite
     * @throws Exception si le dépôt est invalide
     */
    private function setDepotQuantite($productId, $depot, $quantite) {
        $this->validateDepot($depot);

        // product_depot.quantite est UNSIGNED : un négatif ferait échouer la requête. Plafond à 0 pour que ce soit vrai quel que soit l'appelant.
        $quantite = max(0, (int) $quantite);

        $sql = "INSERT INTO product_depot (product_id, depot, quantite) VALUES (:product_id, :depot, :quantite)
        ON DUPLICATE KEY UPDATE quantite = :quantite_update";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':product_id'      => (int) $productId,
            ':depot'           => $depot,
            ':quantite'        => $quantite,
            ':quantite_update' => $quantite,
        ]);
    }

    /**
     * Recalculer products.quantite comme la somme de tous les dépôts. 
     * A appeler après toute écriture sur product_depot. 
     * COALESCE pour l'import qui n'insère pas de ligne dans dépot. 
     * 
     * @param int $productId
     */
    private function recalculerTotal($productId) {
        $sql = "UPDATE products 
            SET quantite = COALESCE((SELECT SUM(quantite) FROM product_depot WHERE product_id = :pid), 0) WHERE id = :id";
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute([
            ':pid' => (int) $productId,
            ':id'  => (int) $productId,
        ]);
    }

    /**
     * Ajuster le stock d'un dépôt en lui appliquant une VARIATION signée,
     * de façon atomique (l'addition est faite par la base, jamais lue puis
     * réécrite en PHP). Delta positif = on ajoute, négatif = on retire.
     * Le stock ne descend jamais sous 0 (GREATEST). Crée la ligne dépôt si
     * elle n'existe pas encore.
     *
     * @param int $productId
     * @param string $depot 'tours_nord' ou 'tours_centre'
     * @param int $delta variation à appliquer (positive ou négative)
     * @throws Exception si le dépôt est invalide
     */
    private function ajusterDepotQuantite($productId, $depot, $delta) {
        $this->validateDepot($depot);

        $delta = (int) $delta;

        // Si la ligne n'existe pas encore, on part de max(0, delta) : un delta négatif ne doit pas insérer une valeur négative (colonne UNSIGNED).
        $initiale = max(0, $delta);

        $sql = "INSERT INTO product_depot (product_id, depot, quantite)
                VALUES (:product_id, :depot, :initiale)
                ON DUPLICATE KEY UPDATE quantite = GREATEST(0, quantite + :delta)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':product_id' => (int) $productId,
            ':depot'      => $depot,
            ':initiale'   => $initiale,
            ':delta'      => $delta,
        ]);
    }

    /**
     * Renvoyer la quantité actuelle d'un produit dans un dépôt (0 si aucune ligne)
     * @param int $productId
     * @param string $depot
     * @return int 
     */
    private function getDepotQuantite($productId, $depot) {
        $sql = "SELECT quantite FROM product_depot WHERE product_id = :pid AND depot = :depot";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => (int) $productId, ':depot' => $depot]);

        $q = $stmt->fetchColumn();  // false si aucune ligne
        return $q === false ? 0 : (int) $q;
    }
}