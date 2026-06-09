<?php

class BobProduct
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function search($filters, $page = 1, $limit = 20)
    {
        $ean         = $filters['ean']         ?? null;
        $libelle     = $filters['libelle']     ?? null;
        $fournisseur = $filters['fournisseur'] ?? null;

        if (empty($ean) && empty($libelle) && empty($fournisseur)) {
            throw new Exception("Veuillez fournir au moins un critère de recherche");
        }

        $sql    = "SELECT * FROM bob_products WHERE 1=1";
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

        $sqlCount = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

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
}