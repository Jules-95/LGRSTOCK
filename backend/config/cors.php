<?php
/**
 * ============================================================
 * CONFIGURATION CORS
 * ============================================================
 * 
 * RECOIT : 
 * - Toutes les requêtes HTTP vers les endpoints API 
 * 
 * CE QU'IL FAIT : 
 * - Définit les headers CORS sur chaque réponse
 * - Répond immédiatement aux requêtes OPTIONS (preflight)
 * 
 * CE QU'IL DESSERT : 
 * - Inclus dans tous les endpoints via require_once
 */

// Lire l'origine autorisée depuis .env
// $_ENV est déjà chargé par database.php si inclus avant,
// sinon on relit le .env directement
if (empty($_ENV['CORS_ORIGIN'])) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$allowed_origin = $_ENV['CORS_ORIGIN'] ?? 'http://localhost:5173';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowed_origin) {
    header('Access-Control-Allow-Origin: ' . $allowed_origin);
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Headers de sécurité
header('X-Content-Type-Options: nosniff');  // Empêche le navigateur de deviner le type de fichier
header('X-Frame-Options: DENY');             // Empêche l'app d'être chargée dans une iframe (clickjacking)
header('Referrer-Policy: strict-origin-when-cross-origin'); // Limite les infos envoyées dans le header Referer

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}