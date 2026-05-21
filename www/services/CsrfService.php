<?php

class CsrfService {
    private const TOKEN_KEY      = 'csrf_token';
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    private const TOKEN_LIFETIME = 3600;
    private const TOKEN_LENGTH   = 32;

    public static function generateToken(): string {
        if (empty($_SESSION[self::TOKEN_KEY]) || self::isExpired()) {
            $_SESSION[self::TOKEN_KEY]      = bin2hex(random_bytes(self::TOKEN_LENGTH));
            $_SESSION[self::TOKEN_TIME_KEY] = time();
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    public static function validateToken(string $token): bool {
        if (empty($_SESSION[self::TOKEN_KEY]) || self::isExpired()) {
            return false;
        }
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    public static function invalidateToken(): void {
        unset($_SESSION[self::TOKEN_KEY], $_SESSION[self::TOKEN_TIME_KEY]);
    }

    private static function isExpired(): bool {
        if (empty($_SESSION[self::TOKEN_TIME_KEY])) return true;
        return (time() - $_SESSION[self::TOKEN_TIME_KEY]) > self::TOKEN_LIFETIME;
    }
}
