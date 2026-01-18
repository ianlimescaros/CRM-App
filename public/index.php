<?php
// Web front controller; handles page routing and auth gate.

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/services/AuthService.php';

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
    'tenancy-contracts',
    'noc-leasing',
];

$page = $_GET['page'] ?? 'login';
$page = in_array($page, $allowedPages, true) ? $page : 'login';

// Pages allowed without auth
$publicPages = ['login'];

// 🔒 Protect everything else using auth_token cookie
if (!in_array($page, $publicPages, true)) {
    $token = $_COOKIE['auth_token'] ?? null;

    if (!$token) {
        header('Location: /index.php?page=login');
        exit;
    }

    $auth = new AuthService();
    $user = $auth->requireAuth($token);

    if (!$user) {
        // Token invalid/expired → clear cookie and redirect
        setcookie('auth_token', '', time() - 3600, '/');
        header('Location: /index.php?page=login');
        exit;
    }
}

$viewPath = __DIR__ . '/views/' . $page . '.php';

ob_start();
require $viewPath;
$content = ob_get_clean();

require __DIR__ . '/views/layout.php';
