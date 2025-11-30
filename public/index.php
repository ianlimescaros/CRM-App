<?php

$page = $_GET['page'] ?? 'login';
$viewPath = __DIR__ . '/views/' . $page . '.php';

if (!file_exists($viewPath)) {
    $viewPath = __DIR__ . '/views/login.php';
}

ob_start();
require $viewPath;
$content = ob_get_clean();

require __DIR__ . '/views/layout.php';
