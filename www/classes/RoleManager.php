<?php
class RoleManager {
    private PDO $pdo;
    private array $allowedRoles = ['passager', 'chauffeur', 'chauffeur_passager'];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function updateRole(int $userId, string $role): array {
        if (!in_array($role, $this->allowedRoles)) {
            return ['success' => false, 'error' => 'Rôle invalide.'];
        }

        try {
            if ($this->roleExists($userId)) {
                $stmt = $this->pdo->prepare("UPDATE roles_utilisateurs SET role = ? WHERE utilisateur_id = ?");
                $stmt->execute([$role, $userId]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO roles_utilisateurs (utilisateur_id, role) VALUES (?, ?)");
                $stmt->execute([$userId, $role]);
            }

            return ['success' => true, 'role' => $this->getRole($userId)];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()];
        }
    }

    private function roleExists(int $userId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM roles_utilisateurs WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
        return (bool) $stmt->fetchColumn();
    }

    private function getRole(int $userId): ?string {
        $stmt = $this->pdo->prepare("SELECT role FROM roles_utilisateurs WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row['role'] ?? null;
    }
}