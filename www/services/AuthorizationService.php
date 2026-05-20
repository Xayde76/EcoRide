<?php

class AuthorizationService {
    public const ROLE_ADMIN  = 1;
    public const ROLE_EMPLOYE = 2;
    public const ROLE_USER   = 3;

    private PDO $pdo;
    private ?int $userId;

    public function __construct(PDO $pdo, ?int $userId = null) {
        $this->pdo    = $pdo;
        $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
    }

    private function hasRole(int $roleId): bool {
        if (!$this->userId) return false;
        $stmt = $this->pdo->prepare("SELECT role_id FROM utilisateurs WHERE id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchColumn() === $roleId;
    }

    public function isAdmin(): bool {
        return $this->hasRole(self::ROLE_ADMIN);
    }
}
