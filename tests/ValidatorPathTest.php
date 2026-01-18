<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/services/Validator.php';

class ValidatorPathTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('TESTING=1');
        // ensure storage/uploads exists for the test
        $uploads = __DIR__ . '/../storage/uploads/test_tmp_dir';
        @mkdir($uploads, 0775, true);
    }

    public function testIsPathInUploadsPositive(): void
    {
        $uploadsRoot = realpath(__DIR__ . '/../storage/uploads');
        $tmp = $uploadsRoot . '/validator_test_' . bin2hex(random_bytes(4));
        file_put_contents($tmp, 'x');
        $this->assertTrue(Validator::isPathInUploads($tmp));
        @unlink($tmp);
    }

    public function testIsPathInUploadsNegative(): void
    {
        $outside = sys_get_temp_dir() . '/validator_outside_' . bin2hex(random_bytes(4));
        file_put_contents($outside, 'x');
        $this->assertFalse(Validator::isPathInUploads($outside));
        @unlink($outside);
    }
}
