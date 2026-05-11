<?php

class ImportController
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
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        // Lire toutes les lignes
        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count(array_filter($row)) === 0) continue; // ignorer lignes vides
            $rows[] = array_combine($headers, $row);
        }
        fclose($handle);

        // PASSE 1 — Validation
        $errors = $this->validate($rows);
        if (!empty($errors)) {
            $this->sendResponse(422, true, 'Erreurs de validation', $errors);
            return;
        }

        // PASSE 2 — Insertion
        $imported = $this->insertOrUpdate($rows);

        $this->sendResponse(200, false, "$imported produit(s) importé(s) avec succès");
    }

    private function validate(array $rows): array
    {
        $errors = [];
        $eansInFile = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // +2 car ligne 1 = en-têtes

            $ean = trim($row['ean'] ?? '');

            // EAN obligatoire
            if (empty($ean)) {
                $errors[] = "Ligne $line : EAN manquant";
                continue;
            }

            // EAN en doublon dans le fichier lui-même
            if (in_array($ean, $eansInFile)) {
                $errors[] = "Ligne $line : EAN $ean en doublon dans le fichier";
                continue;
            }
            $eansInFile[] = $ean;

            // Libellé obligatoire si EAN inconnu en base
            $existsInDb = $this->eanExists($ean);
            $libelle = trim($row['libelle'] ?? '');
            if (!$existsInDb && empty($libelle)) {
                $errors[] = "Ligne $line : libellé obligatoire pour un nouveau produit (EAN : $ean)";
            }
        }

        return $errors;
    }

    private function insertOrUpdate(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $ean      = trim($row['ean']);
            $libelle  = trim($row['libelle'] ?? '');
            $fournisseur     = trim($row['fournisseur'] ?? '') ?: null;
            $ref_fournisseur = trim($row['ref_fournisseur'] ?? '') ?: null;
            $prix     = isset($row['prix']) && $row['prix'] !== '' ? (float) str_replace(',', '.', $row['prix']) : null;
            $quantite = isset($row['quantite']) && $row['quantite'] !== '' ? (int) $row['quantite'] : 0;

            $exists = $this->eanExists($ean);

            if ($exists) {
                // Mise à jour — on ne touche qu'aux champs présents
                $fields = ['quantite = :quantite'];
                $params = [':ean' => $ean, ':quantite' => $quantite];

                if ($libelle !== '')       { $fields[] = 'libelle = :libelle';               $params[':libelle'] = $libelle; }
                if ($fournisseur !== null) { $fields[] = 'fournisseur = :fournisseur';         $params[':fournisseur'] = $fournisseur; }
                if ($ref_fournisseur !== null) { $fields[] = 'ref_fournisseur = :ref_fournisseur'; $params[':ref_fournisseur'] = $ref_fournisseur; }
                if ($prix !== null)        { $fields[] = 'prix = :prix';                      $params[':prix'] = $prix; }

                $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE ean = :ean';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                // Insertion
                $stmt = $this->pdo->prepare(
                    'INSERT INTO products (ean, libelle, fournisseur, ref_fournisseur, prix, quantite)
                     VALUES (:ean, :libelle, :fournisseur, :ref_fournisseur, :prix, :quantite)'
                );
                $stmt->execute([
                    ':ean'             => $ean,
                    ':libelle'         => $libelle,
                    ':fournisseur'     => $fournisseur,
                    ':ref_fournisseur' => $ref_fournisseur,
                    ':prix'            => $prix,
                    ':quantite'        => $quantite,
                ]);
            }

            $count++;
        }

        return $count;
    }

    private function eanExists(string $ean): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM products WHERE ean = :ean');
        $stmt->execute([':ean' => $ean]);
        return $stmt->fetch() !== false;
    }

    private function sendResponse(int $status, bool $error, string $message, array $details = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'error'   => $error,
            'message' => $message,
            'details' => $details,
        ]);
    }
}