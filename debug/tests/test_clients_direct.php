<?php
// Debug: Direct API call simulation
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/src/models/Client.php';

// Simulate authenticated request
echo "=== DIRECT CLIENT QUERY TEST ===\n\n";

// Test with Stanley (user 10)
$userId = 10;

// Test 1: Raw SQL
echo "Test 1: Raw SQL Query\n";
$db = db();
$result = $db->query('SELECT * FROM clients WHERE user_id = ' . $userId);
$rows = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Raw query returned: " . count($rows) . " rows\n";
foreach ($rows as $row) {
    echo "  - " . $row['full_name'] . "\n";
}

// Test 2: Client::all() method
echo "\nTest 2: Client::all() Method\n";
$pagination = [
    'limit' => 20,
    'offset' => 0,
    'order_by' => 'created_at',
    'order_dir' => 'DESC',
];
$filters = [];

$clients = Client::all($userId, $pagination, $filters);
echo "Client::all() returned: " . count($clients) . " rows\n";
echo "Client names: " . json_encode(array_column($clients, 'full_name'), JSON_UNESCAPED_UNICODE) . "\n";

// Test 3: Client::countAll() method
echo "\nTest 3: Client::countAll() Method\n";
$total = Client::countAll($userId, $filters);
echo "Client::countAll() returned: " . $total . "\n";

// Test 4: Check PDO attributes
echo "\nTest 4: PDO Configuration\n";
echo "ATTR_DEFAULT_FETCH_MODE: " . $db->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) . "\n";
echo "ATTR_ERRMODE: " . $db->getAttribute(PDO::ATTR_ERRMODE) . "\n";

// Test 5: Check connection
echo "\nTest 5: Connection Check\n";
echo "PDO Instance: " . get_class($db) . "\n";
echo "Database accessible: YES\n";
?>
