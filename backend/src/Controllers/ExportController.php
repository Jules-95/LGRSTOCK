<?php 

class ExportController {
    private PDO $pdo; 

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

    }

    public function export(): void {
        $stmt = $this->pdo->query(
            'SELECT ean, libelle, fournisseur, ref_fournisseur, prix, quantite FROM products ORDER BY libelle ASC'
        );

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stock_lgr_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output' , 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Dit a Excel que ce fichier est en UTF-8

        fputcsv($output, ['EAN', 'Libellé', 'Fournisseur', 'Réf. fournisseur', 'Prix', 'Quantité'], ';'); //  le troisième paramètre ';' c'est le séparatuer car on utilise le ; la virgule etant réservée aux décimaux

        foreach ($products as $product) {
            fputcsv($output, [
                '="' . ($product['ean'] ?? '') . '"',
                //Formulation différente pour EAN - A l'ouverture d'un CSV par Excel il reconnait un nombre et le converti en entier. On force excel a évaluer ça comme une formule et affiche la valeur telle quelle avec le 0 
                // Problème connu des formats de code EAN sur Excel. 
                $product['libelle'],
                $product['fournisseur'] ?? '',
                $product['ref_fournisseur'] ?? '',
                $product['prix'] ?? '',
                $product['quantite'] ?? 0,
            ], ';');
        }
        fclose($output);
        // Pas obligatoire ici mais fopen ouvre un "canal" vers php://output et lui alloue des ressources en mémoire - Même si dans ce cas attendre la fin du script pour libérer ces ressoucres c'est mieux de fermer manuellement. 
    }
}