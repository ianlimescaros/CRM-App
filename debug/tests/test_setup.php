<?php

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         CRM LOCAL TEST SETUP DIAGNOSTICS                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Check .env file
echo "✓ TEST 1: Environment Configuration\n";
if (file_exists('.env')) {
    echo "  ✅ .env file exists\n";
    $env = parse_ini_file('.env');
    echo "  DB_HOST: " . ($env['DB_HOST'] ?? 'NOT SET') . "\n";
    echo "  DB_NAME: " . ($env['DB_NAME'] ?? 'NOT SET') . "\n";
    echo "  DB_USER: " . ($env['DB_USER'] ?? 'NOT SET') . "\n";
} else {
    echo "  ❌ .env file NOT found\n";
    exit;
}

// Test 2: Database connection
echo "\n✓ TEST 2: Database Connection\n";
try {
    require 'src/config/database.php';
    $db = db();
    echo "  ✅ Database connected\n";
    
    // Check if tables exist
    $tables = $db->query("SHOW TABLES FROM " . ($env['DB_NAME'] ?? 'crm_app'))->fetchAll();
    echo "  Tables found: " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "    - " . $t[0] . "\n";
    }
} catch (Exception $e) {
    echo "  ❌ Database Error: " . $e->getMessage() . "\n";
    echo "  Make sure MySQL is running and credentials in .env are correct\n";
    exit;
}

// Test 3: Check users
echo "\n✓ TEST 3: Sample Data\n";
try {
    $users = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch();
    $userCount = $users['cnt'] ?? 0;
    echo "  Users in database: " . $userCount . "\n";
    
    if ($userCount == 0) {
        echo "  ⚠️  No users found. You need to register first:\n";
        echo "    POST http://localhost:8000/api.php?page=register\n";
        echo "    Body: {\"name\":\"Test\",\"email\":\"test@example.com\",\"password\":\"test123\"}\n";
    } else {
        $user = $db->query("SELECT id, name, email FROM users LIMIT 1")->fetch();
        echo "    Sample user: {$user['name']} ({$user['email']})\n";
        
        $clients = $db->query("SELECT COUNT(*) as cnt FROM clients WHERE user_id = " . $user['id'])->fetch();
        $clientCount = $clients['cnt'] ?? 0;
        echo "\n  Clients for user {$user['id']}: " . $clientCount . "\n";
        
        if ($clientCount == 0) {
            echo "  ⚠️  No clients found. Create one via API:\n";
            echo "    POST http://localhost:8000/api.php?page=clients\n";
            echo "    Body: {\"full_name\":\"John Doe\",\"email\":\"john@example.com\",\"phone\":\"1234567\"}\n";
        }
    }
} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: API test
echo "\n✓ TEST 4: API Test\n";
echo "  Start the server with:\n";
echo "    php -S localhost:8000 -t public\n";
echo "\n  Then test login:\n";
echo "    curl -Method POST http://localhost:8000/api.php?page=login \\\n";
echo "      -Headers @{\"Content-Type\"=\"application/json\"} \\\n";
echo "      -Body '{\"email\":\"test@example.com\",\"password\":\"test123\"}'\n";

echo "\n✓ ALL CHECKS COMPLETE\n";
