<?php

/**
 * Controller qui gère les statistiques globales du dashboard admin.
 * 
 * Pas de Model associé : Les requêtes SQL sont directement ici car  ce sont de simples COUNT(*) sans logique metier complexe
 */

class StatsController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getStats() {
        try {
            $totalProduits = (int) $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $produitsRupture = (int) $this->pdo->query("SELECT COUNT(*) FROM products WHERE quantite = 0")->fetchColumn();
            $totalUtilisateurs = (int) $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => [
                    'total_produits' => $totalProduits,
                    'produits_rupture' => $produitsRupture,
                    'total_utilisateurs' => $totalUtilisateurs
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}