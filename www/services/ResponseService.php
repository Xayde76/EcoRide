<?php

class ResponseService {
    public static function success(mixed $data = null, string $message = null, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');

        $response = ['success' => true];
        if ($data !== null) {
            $response['data'] = $data;
        }
        if ($message !== null) {
            $response['message'] = $message;
        }

        echo json_encode($response);
        exit;
    }

    public static function error(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    public static function unauthorized(string $message = 'Unauthorized'): void {
        self::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void {
        self::error($message, 403);
    }

    public static function notFound(string $message = 'Not found'): void {
        self::error($message, 404);
    }

    public static function validationError(string $message = 'Validation failed', array $errors = []): void {
        http_response_code(400);
        header('Content-Type: application/json');
        $response = [
            'success' => false,
            'error' => $message
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        echo json_encode($response);
        exit;
    }

    public static function serverError(string $message = 'Internal server error'): void {
        self::error($message, 500);
    }
}
