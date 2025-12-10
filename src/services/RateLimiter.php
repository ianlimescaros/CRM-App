<?php

class RateLimiter
{
    /**
     * Very small file-based rate limiter to throttle brute force attempts.
     * Uses per-key counters with a sliding window defined by $decaySeconds.
     */
    public static function hit(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/ratelimits';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir . '/' . sha1($key) . '.json';
        $now = time();
        $data = [
            'count' => 0,
            'expires_at' => $now + $decaySeconds,
        ];

        if (is_file($file)) {
            $json = file_get_contents($file);
            $stored = json_decode((string)$json, true);
            if (is_array($stored)) {
                if (!empty($stored['expires_at']) && $stored['expires_at'] > $now) {
                    $data = $stored;
                }
            }

            if ($now > ($data['expires_at'] ?? 0)) {
                $data = [
                    'count' => 0,
                    'expires_at' => $now + $decaySeconds,
                ];
            }
        }

        if (($data['count'] ?? 0) >= $maxAttempts) {
            return false;
        }

        $data['count'] = ($data['count'] ?? 0) + 1;
        if (empty($data['expires_at']) || $data['expires_at'] < $now) {
            $data['expires_at'] = $now + $decaySeconds;
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }
}
