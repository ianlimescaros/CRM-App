<?php
require 'src/config/database.php';
try {
    $db = db();
    
    // Get all users
    $users = $db->query('SELECT id, name, email FROM users')->fetchAll();
    echo '=== USERS ===' . PHP_EOL;
    foreach ($users as $u) {
        echo '  ID: ' . $u['id'] . ' | ' . $u['name'] . ' (' . $u['email'] . ')' . PHP_EOL;
    }
    
    // Get clients for each user
    echo PHP_EOL . '=== CLIENTS ===' . PHP_EOL;
    foreach ($users as $u) {
        $clients = $db->query('SELECT id, full_name, email FROM clients WHERE user_id = ' . $u['id'])->fetchAll();
        echo 'User ' . $u['id'] . ': ' . count($clients) . ' clients' . PHP_EOL;
        foreach ($clients as $c) {
            echo '  - ' . $c['full_name'] . ' (' . $c['email'] . ')' . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
