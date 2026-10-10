<?php 

/**
 * Model Inventory : gère les sessions d'inventaire d'un dépot 
 * - Inventaire "en cours" tant que ended_at est NULL
 * - Un seul inventaire en cours par dépot 
 * - quand un inventaire est lancé -> Stock à 0 (Pas d'impact sur l'autre dépot)
 */

class Inventory {
    private $pdo;

    private const DEPOTS = ['tours_nord', 'tours_centre'];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // METHODES PUBLIQUES

    /**
     * Renvoie l'inventaire en cours d'un dépot 
     * @param string $depot 'tours_nord' ou 'tours_centre'
     * @return array|null ['id', 'depot', 'started_at', 'started_by'] ou null si aucun
     */

    public function getEnCours($depot) {
        $this->validateDepot($depot);

        $sql = "SELECT inventories.id, inventories.depot, inventories.started_at, users.username AS started_by
        FROM inventories
        LEFT JOIN users ON users.id = inventories.started_by
        WHERE inventories.depot = :depot AND inventories.ended_at IS NULL
        LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':depot' => $depot]);

        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

        return $inventory ?: null;
    }

    /**
     * Démarer un inventaire (Remise à 0 du stock, recalcul des totaux)
     * @param string $depot 
     * @param int $userId utilisateur qui lance l'inventaire 
     * @return array l'inventaire créé 
     * @throws Exception si un inventaire est déja en cours 
     *
     */

    public function demarrer($depot, $userId) {
        $this->validateDepot($depot);

        // Garde-fou : Pas de remise à 0 pdt un inventaire
        if ($this->getEnCours($depot)) {
            throw new Exception("Un inventaire est déja en cours pour ce dépot");
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Remise à 0 du stock du dépot
            $stmt = $this->pdo->prepare("UPDATE product_depot SET quantite = 0 WHERE depot = :depot");
            $stmt->execute([':depot' => $depot]);

            // 2. Recalcul de tous les totaux (total = somme des 2 dépôts)
            $this->pdo->exec("UPDATE products
                SET quantite = COALESCE((SELECT SUM(pd.quantite) FROM product_depot pd WHERE pd.product_id = products.id), 0)");

            // 3. Ouverture de la session d'inventaire
            $stmt = $this->pdo->prepare("INSERT INTO inventories (depot, started_by) VALUES (:depot, :user_id)");
            $stmt->execute([
                ':depot'    => $depot,
                ':user_id'  => (int) $userId,
            ]);

            $this->pdo->commit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur technique : " . $e->getMessage());
        }

        return $this->getEnCours($depot);
    }

    /**
     * Terminer l'inventaire en cours d'un dépot
     * @param string $depot
     * @return bool true si l'inventaire a été cloturé
     * @throws Exception si aucun inventaire n'est en cours
     */
    public function terminer($depot) {
        $this->validateDepot($depot);

        $stmt = $this->pdo->prepare("UPDATE inventories SET ended_at = NOW() WHERE depot = :depot AND ended_at IS NULL");
        $stmt->execute([':depot' => $depot]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Aucun inventaire en cours pour ce dépot");
        }

        return true;
    }


    // METHODES PRIVATE
    private function validateDepot($depot) {
        if (!in_array($depot, self::DEPOTS, true)) {
            throw new Exception("Dépot invalide");
        }
    }

}