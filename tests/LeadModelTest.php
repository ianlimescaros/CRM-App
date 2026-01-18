<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/models/Lead.php';
require_once __DIR__ . '/../src/models/User.php';

class LeadModelTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('TESTING=1');
        // Ensure DB is initialized
        db();
        // Clean tables
        db()->exec('DELETE FROM leads');
        db()->exec('DELETE FROM users');
    }

    public function testCreateAndSearchLead(): void
    {
        $userId = \User::create('Test User', 'test@local', password_hash('pass', PASSWORD_BCRYPT));
        $leadId1 = \Lead::create($userId, ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'notes' => 'Interested in 2 Bedroom']);
        $leadId2 = \Lead::create($userId, ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'notes' => 'Looking for studio']);

        $results = \Lead::all($userId, ['search' => 'alice'], ['limit' => 10, 'offset' => 0, 'order_by' => 'created_at', 'order_dir' => 'DESC']);
        $this->assertNotEmpty($results, 'Search should find Alice');
        $this->assertEquals('Alice Johnson', $results[0]['name']);

        $results2 = \Lead::all($userId, ['search' => 'studio'], ['limit' => 10, 'offset' => 0, 'order_by' => 'created_at', 'order_dir' => 'DESC']);
        $this->assertNotEmpty($results2, 'Search should find studio note');
        $this->assertEquals('Bob Smith', $results2[0]['name']);
    }
}
