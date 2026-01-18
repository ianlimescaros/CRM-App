<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/services/Response.php';

class ResponseSecurityHeadersTest extends TestCase
{
    public function testEmitSecurityHeadersAddsRecommendedHeaders(): void
    {
        // Call the emitter directly (it does not exit)
        Response::emitSecurityHeaders();
        $headers = array_map('strtolower', headers_list());

        $this->assertTrue(
            array_reduce(['x-content-type-options: nosniff', 'x-frame-options: deny', 'referrer-policy: no-referrer-when-downgrade'], function($carry, $h) use ($headers) {
                return $carry && in_array($h, $headers, true);
            }, true),
            'Expected security headers to be present'
        );
    }
}
