<?php

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidateUploadRejectsMissingFile(): void
    {
        $res = Validator::validateUpload([]);
        $this->assertArrayHasKey('file', $res);
    }

    public function testValidateUploadAcceptsSmallPngTmpFile(): void
    {
        $tmp = sys_get_temp_dir() . '/test-upload-' . bin2hex(random_bytes(4)) . '.png';
        // simple 1x1 PNG header (valid minimal PNG)
        $png = hex2bin('89504e470d0a1a0a0000000d4948445200000001000000010802000000');
        file_put_contents($tmp, $png);

        $file = [
            'name' => 'small.png',
            'tmp_name' => $tmp,
            'size' => filesize($tmp),
        ];

        $res = Validator::validateUpload($file, ['png'], 1024 * 1024, 'file');
        // Accepts when extension + mime match
        $this->assertSame([], $res);

        @unlink($tmp);
    }

    public function testValidateUploadRejectsLargeFile(): void
    {
        $tmp = sys_get_temp_dir() . '/test-upload-' . bin2hex(random_bytes(4)) . '.dat';
        file_put_contents($tmp, random_bytes(1024 * 1024 + 10));
        $file = ['name' => 'big.dat', 'tmp_name' => $tmp, 'size' => filesize($tmp)];
        $res = Validator::validateUpload($file, ['dat'], 1024 * 1024, 'file');
        $this->assertArrayHasKey('file', $res);
        @unlink($tmp);
    }
}
