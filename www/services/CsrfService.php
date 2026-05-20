<?php

class CsrfService {
    private const TOKEN_KEY      = 'csrf_token';
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    private const TOKEN_LIFETIME = 3600;
    private const TOKEN_LENGTH   = 32;

    public static function generateToken(): string {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY]      = bin2hex(random_bytes(self::TOKEN_LENGTH));
            $_SESSION[self::TOKEN_TIME_KEY] = time();
        }
        return $_SESSION[self::TOKEN_KEY];
    }
}
