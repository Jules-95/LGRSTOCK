<?php 
/**
 * Headers CORS - inclus dans tous les endpoints
 * Centralise la politique d'accès cross-origin
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');