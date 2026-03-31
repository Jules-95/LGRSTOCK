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

$allowed_origin = 'http://localhost:5173'; // A mettre à jour en production

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowed_origin) {
    header('Access-Control-Allow-Origin: ' . $allowed_origin);
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Le navigateur envoie une requête OPTION pour chaque requête 
// cross-origin pour vérifier les permissions (preflight). 
// Réponse immédiate sans traitement supplémentaire
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}