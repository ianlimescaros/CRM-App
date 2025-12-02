<?php

$allowedPages = [
    'login',
    'dashboard',
    'leads',
    'contacts',
    'client-profile',
    'profile',
    'deals',
    'tasks',
    'reports',
    'ai-assistant',
];

$page = $_GET['page'] ?? 'login';
$page = in_array($page, $allowedPages, true) ? $page : 'login';
$viewPath = __DIR__ . '/views/' . $page . '.php';

ob_start();
require $viewPath;
$content = ob_get_clean();

require __DIR__ . '/views/layout.php';
