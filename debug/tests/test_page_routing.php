<?php
// Check what page is being rendered
session_start();
require_once __DIR__ . '/src/services/AuthService.php';

$allowedPages = [
    'login',
    'dashboard',
    'leads',
    'offplan-leads',
    'contacts',
    'client-profile',
    'profile',
    'deals',
    'tasks',
    'reports',
    'ai-assistant',
    'tenancy-contracts',
    'noc-leasing',
    'rental-agreements',
];

$page = $_GET['page'] ?? 'login';
$pageRaw = $_GET['page'] ?? 'login';
$page = in_array($page, $allowedPages, true) ? $page : 'login';

echo "=== PAGE ROUTING DEBUG ===\n";
echo "Raw \$_GET['page']: '$pageRaw'\n";
echo "After validation: '$page'\n";
echo "Page allowed: " . (in_array($page, $allowedPages, true) ? "YES" : "NO") . "\n";
echo "\nWill load clients.js: " . (($page ?? '') === 'contacts' ? 'YES ✓' : 'NO ✗') . "\n";

// Check auth
$token = $_COOKIE['auth_token'] ?? null;
echo "\nAuth token: " . ($token ? 'PRESENT' : 'MISSING') . "\n";

if ($token) {
    $auth = new AuthService();
    $user = $auth->requireAuth($token);
    if ($user) {
        echo "User: " . $user['email'] . " (ID: " . $user['id'] . ")\n";
    } else {
        echo "User: TOKEN INVALID\n";
    }
}
?>
