<?php

class RoleManager extends BaseManager {
    private const ALLOWED_ROLES = ['passager', 'chauffeur', 'chauffeur_passager'];

    public function updateRole(int $userId, string $role, int $adminId): array {
        try {
            if (!ValidationService::validateInteger($userId, 1)) {
                return ['success' => false, 'error' => 'User ID invalide.'];
            }

            if (!ValidationService::validateInArray($role, self::ALLOWED_ROLES)) {
                return ['success' => false, 'error' => 'Rôle invalide.'];
            }

            $authorization = new AuthorizationService($this->pdo, $adminId);
            if (!$authorization->isAdmin()) {
                LoggerService::security('Unauthorized role update attempt', ['admin_id' => $adminId, 'user_id' => $userId]);
                throw new AuthorizationException('Unauthorized');
            }

            $stmt = $this->prepare("SELECT id FROM utilisateurs WHERE id = ?");
            $this->execute($stmt, [$userId]);
            if (!$this->fetch($stmt)) {
                return ['success' => false, 'error' => 'User not found.'];
            }

            if ($this->roleExists($userId)) {
                $stmt = $this->prepare("UPDATE roles_utilisateurs SET role = ? WHERE utilisateur_id = ?");
                $this->execute($stmt, [$role, $userId]);
            } else {
                $stmt = $this->prepare("INSERT INTO roles_utilisateurs (utilisateur_id, role) VALUES (?, ?)");
                $this->execute($stmt, [$userId, $role]);
            }

            LoggerService::security('Role updated', ['admin_id' => $adminId, 'user_id' => $userId, 'new_role' => $role]);

            return [
                'success' => true,
                'message' => 'Rôle mis à jour avec succès.',
                'role' => $role
            ];
        } catch (AuthorizationException $e) {
            return ['success' => false, 'error' => 'Accès refusé.'];
        } catch (\PDOException $e) {
            LoggerService::error('Role update error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour.'];
        }
    }

    private function roleExists(int $userId): bool {
        $stmt = $this->prepare("SELECT COUNT(*) FROM roles_utilisateurs WHERE utilisateur_id = ?");
        $this->execute($stmt, [$userId]);
        return (bool)$this->fetchColumn($stmt);
    }
}