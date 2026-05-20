<?php

class ValidationService {
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword(string $password): bool {
        return strlen($password) >= 6;
    }

    public static function validateDate(string $dateString): bool {
        $normalized = str_replace('T', ' ', $dateString);
        $date = \DateTime::createFromFormat('Y-m-d H:i', $normalized);
        return $date !== false && $date > new \DateTime();
    }

    public static function validateNumber(mixed $value, ?float $min = null, ?float $max = null): bool {
        if (!is_numeric($value)) {
            return false;
        }
        $num = (float)$value;
        if ($min !== null && $num < $min) {
            return false;
        }
        if ($max !== null && $num > $max) {
            return false;
        }
        return true;
    }

    public static function validateInteger(mixed $value, ?int $min = null, ?int $max = null): bool {
        if (!is_numeric($value) || strpos((string)$value, '.') !== false) {
            return false;
        }
        $num = (int)$value;
        if ($min !== null && $num < $min) {
            return false;
        }
        if ($max !== null && $num > $max) {
            return false;
        }
        return true;
    }

    public static function validateString(string $value, ?int $minLength = null, ?int $maxLength = null): bool {
        $len = strlen($value);
        if ($minLength !== null && $len < $minLength) {
            return false;
        }
        if ($maxLength !== null && $len > $maxLength) {
            return false;
        }
        return true;
    }

    public static function validateInArray(mixed $value, array $allowedValues): bool {
        return in_array($value, $allowedValues, true);
    }

    public static function sanitizeString(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
