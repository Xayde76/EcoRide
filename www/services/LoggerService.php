<?php

class LoggerService {
    private const LOG_DIR = __DIR__ . '/../logs';
    private const LOG_FILE = 'app.log';

    public static function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'guest';
        $contextStr = !empty($context) ? json_encode($context) : '';

        $logMessage = sprintf(
            "[%s] %s - User: %s - %s %s\n",
            $timestamp,
            strtoupper($level),
            $userId,
            $message,
            $contextStr
        );

        $logFile = self::LOG_DIR . '/' . date('Y-m-d') . '_' . self::LOG_FILE;

        @error_log($logMessage, 3, $logFile);
    }

    public static function info(string $message, array $context = []): void {
        self::log('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log('warning', $message, $context);
    }

    public static function security(string $message, array $context = []): void {
        self::log('security', $message, $context);
    }
}
