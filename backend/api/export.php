<?php
// backend/api/export.php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Middleware/Auth.php';
require_once __DIR__ . '/../src/Controllers/ExportController.php';

Auth::requireAdmin();

$pdo = getDBConnection();
$controller = new ExportController($pdo);
$controller->export();