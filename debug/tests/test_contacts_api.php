<?php
// Test the contacts/clients API endpoint
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/models/Client.php';

// Stanley is user ID 10 (we know this from previous check_db.php)
$userId = 10;

echo "=== CLIENTS LIST TEST FOR STANLEY (USER ID 10) ===\n\n";

// Test the Client::all() method
$pagination = [
    'limit' => 20,
    'offset' => 0,
    'order_by' => 'created_at',
    'order_dir' => 'DESC',
];

$filters = [];

$total = Client::countAll($userId, $filters);
$clients = Client::all($userId, $pagination, $filters);

echo "Total clients for user $userId: " . $total . "\n";
echo "Returned clients: " . count($clients) . "\n\n";

if (!empty($clients)) {
    echo "Clients returned:\n";
    foreach ($clients as $client) {
        echo "  - ID: " . $client['id'] . " | " . $client['full_name'] . " (" . $client['email'] . ")\n";
    }
} else {
    echo "ERROR: No clients returned! Expected 1 client.\n";
}

echo "\n=== RAW DATABASE CHECK ===\n";
$db = Database::getInstance();
$result = $db->query('SELECT id, user_id, full_name, email FROM clients WHERE user_id = ' . $userId);
$raw = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Raw SQL query for user $userId returned: " . count($raw) . " rows\n";
if (!empty($raw)) {
    echo "Direct DB results:\n";
    foreach ($raw as $row) {
        echo "  - ID: " . $row['id'] . " | " . $row['full_name'] . " (" . $row['email'] . ") - user_id=" . $row['user_id'] . "\n";
    }
} else {
    echo "ERROR: No rows in database for user $userId!\n";
}

echo "\n=== ALL CLIENTS IN DATABASE (NO FILTER) ===\n";
$allClients = $db->query('SELECT id, user_id, full_name, email FROM clients ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
echo "Total clients in database: " . count($allClients) . "\n";
foreach ($allClients as $row) {
    echo "  - ID: " . $row['id'] . " | User: " . $row['user_id'] . " | " . $row['full_name'] . " (" . $row['email'] . ")\n";
}
?>
