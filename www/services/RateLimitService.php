<?php

class RateLimitService {
    private const RATE_LIMIT_KEY_PREFIX = 'rate_limit_';
    private const DEFAULT_LIMIT = 5;
    private const DEFAULT_WINDOW = 900; // 15 minutes

    public static function isRateLimited(string $identifier, int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW): bool {
        $key = self::RATE_LIMIT_KEY_PREFIX . $identifier;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_time' => time()
            ];
            return false;
        }

        $data = $_SESSION[$key];
        $now = time();

        if ($now - $data['first_time'] > $window) {
            $_SESSION[$key] = [
                'count' => 1,
                'first_time' => $now
            ];
            return false;
        }

        if ($data['count'] >= $limit) {
            return true;
        }

        $_SESSION[$key]['count']++;
        return false;
    }

    public static function resetRateLimit(string $identifier): void {
        $key = self::RATE_LIMIT_KEY_PREFIX . $identifier;
        unset($_SESSION[$key]);
    }

    public static function getRateLimitInfo(string $identifier, int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW): array {
        $key = self::RATE_LIMIT_KEY_PREFIX . $identifier;

        if (!isset($_SESSION[$key])) {
            return [
                'remaining' => $limit,
                'reset' => time() + $window
            ];
        }

        $data = $_SESSION[$key];
        $remaining = max(0, $limit - $data['count']);
        $resetTime = $data['first_time'] + $window;

        return [
            'remaining' => $remaining,
            'reset' => $resetTime
        ];
    }
}
