<?php

class BobImportController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function import(): void
    {
        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            $this->sendResponse(400, true, 'Aucun fichier reçu ou erreur d\'upload');
            return;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['csv']['tmp_name']);
        $allowedMimes = ['text/plain', 'text/csv', 'application/csv'];

        if (!in_array($mimeType, $allowedMimes)) {
            $this->sendResponse(400, true, 'Format invalide - seuls les fichiers CSV sont acceptés');
            return;
        }

        $extension = strtolower(pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->sendResponse(400, true, 'Format invalide - Seuls les fichiers CSV sont acceptés');
            return;
        }

        if ($_FILES['csv']['size'] > 15 * 1024 * 1024) {
            $this->sendResponse(400, true, 'Fichier trop volumineux - Maximum 15Mo');
            return;
        }

        $filepath = $_FILES['csv']['tmp_name'];
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $this->sendResponse(500, true, 'Impossible de lire le fichier');
            return;
        }

        // Ignorer le BOM UTF-8 s'il est présent
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Lire la ligne d'en-tête
        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            fclose($handle);
            $this->sendResponse(400, true, 'Fichier CSV vide ou invalide');
            return;
        }

        // Normaliser les en-têtes (minuscules, sans espaces)
        $headers = array_map(fn($h) => mb_strtolower(trim($h), 'UTF-8'), $headers);

        // Mapping colonnes Bob → colonnes table
        $mapping = [
            'libellé article'         => 'libelle',
            'ean'                     => 'ean',
            'fournisseur'             => 'fournisseur',
            'réf. fournisseur'        => 'ref_fournisseur',
            'stock local'             => 'stock_local',
            'prix'                    => 'prix',
            'code article'            => 'code_article',
            'millésime'               => 'millesime',
            'activité'                => 'activite',
            'rayon'                   => 'rayon',
            'famille'                 => 'famille',
            'sous famille'            => 'sous_famille',
            'code produit récréaclub' => 'code_recreaclub',
            'code fournisseur'        => 'code_fournisseur',
        ];

        $headers = array_map(fn($h) => $mapping[$h] ?? $h, $headers);

        // Lire toutes les lignes
        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count(array_filter($row)) === 0) continue;
            $rows[] = array_combine($headers, $row);
        }
        fclose($handle);

        $imported = $this->insert($rows);

        $this->sendResponse(200, false, "$imported ligne(s) importée(s) avec succès");
    }

    private function insert(array $rows): int
    {
        $count = 0;

        $stmt = $this->pdo->prepare(
            'INSERT INTO bob_products 
            (libelle, ean, fournisseur, ref_fournisseur, stock_local, prix, code_article, millesime, activite, rayon, famille, sous_famille, code_recreaclub, code_fournisseur)
            VALUES 
            (:libelle, :ean, :fournisseur, :ref_fournisseur, :stock_local, :prix, :code_article, :millesime, :activite, :rayon, :famille, :sous_famille, :code_recreaclub, :code_fournisseur)'
        );

        foreach ($rows as $row) {
            $ean = trim($row['ean'] ?? '');

            // Padding si EAN numérique incomplet
            if (!empty($ean) && is_numeric($ean) && strlen($ean) < 13) {
                $ean = str_pad($ean, 13, '0', STR_PAD_LEFT);
            }

            // EAN vide → null
            $ean = $ean !== '' ? $ean : null;

            $stmt->execute([
                ':libelle'          => trim($row['libelle'] ?? ''),
                ':ean'              => $ean,
                ':fournisseur'      => trim($row['fournisseur'] ?? '') ?: null,
                ':ref_fournisseur'  => trim($row['ref_fournisseur'] ?? '') ?: null,
                ':stock_local'      => is_numeric($row['stock_local'] ?? '') ? (int)$row['stock_local'] : null,
                ':prix'             => trim($row['prix'] ?? '') ?: null,
                ':code_article'     => trim($row['code_article'] ?? '') ?: null,
                ':millesime'        => trim($row['millesime'] ?? '') ?: null,
                ':activite'         => trim($row['activite'] ?? '') ?: null,
                ':rayon'            => trim($row['rayon'] ?? '') ?: null,
                ':famille'          => trim($row['famille'] ?? '') ?: null,
                ':sous_famille'     => trim($row['sous_famille'] ?? '') ?: null,
                ':code_recreaclub'  => trim($row['code_recreaclub'] ?? '') ?: null,
                ':code_fournisseur' => trim($row['code_fournisseur'] ?? '') ?: null,
            ]);
            $count++;
        }

        return $count;
    }

    private function sendResponse(int $status, bool $error, string $message): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'error'   => $error,
            'message' => $message,
        ]);
    }
}