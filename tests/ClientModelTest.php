<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/models/Client.php';
require_once __DIR__ . '/../src/models/User.php';

class ClientModelTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('TESTING=1');
        db();
        db()->exec('DELETE FROM clients');
        db()->exec('DELETE FROM users');
    }

    public function testCreateAndSearchClient(): void
    {
        $userId = \User::create('Client User', 'client@local', password_hash('pass', PASSWORD_BCRYPT));
        \Client::create($userId, ['full_name' => 'Acme Corporation', 'email' => 'info@acme.com']);
        \Client::create($userId, ['full_name' => 'Beta LLC', 'email' => 'hi@beta.com']);

        $results = \Client::all($userId, ['limit' => 10, 'offset' => 0], ['search' => 'acme']);
        $this->assertNotEmpty($results, 'Search should find Acme');
        $this->assertEquals('Acme Corporation', $results[0]['full_name']);
    }
}
