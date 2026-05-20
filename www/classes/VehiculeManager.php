<?php

class VehiculeManager extends BaseManager {
    private int $userId;
    private const VALID_TYPES = ['essence', 'diesel', 'electrique', 'hybride'];

    public function __construct(PDO $pdo, int $userId) {
        parent::__construct($pdo);
        $this->userId = $userId;
    }

    public function ajouterVehicule(array $data): array {
        try {
            $errors = [];

            if (!ValidationService::validateInArray($data['type_vehicule'] ?? '', self::VALID_TYPES)) {
                $errors['type_vehicule'] = 'Type de véhicule invalide.';
            }

            if (!ValidationService::validateString($data['plaque'] ?? '', 1, 20)) {
                $errors['plaque'] = 'Plaque invalide.';
            }

            if (!ValidationService::validateString($data['modele'] ?? '', 1, 100)) {
                $errors['modele'] = 'Modèle invalide.';
            }

            if (!ValidationService::validateString($data['marque'] ?? '', 1, 100)) {
                $errors['marque'] = 'Marque invalide.';
            }

            if (!ValidationService::validateInteger($data['places'] ?? 0, 1, 10)) {
                $errors['places'] = 'Nombre de places invalide.';
            }

            if (!empty($errors)) {
                throw new ValidationException('Validation failed', $errors);
            }

            $preferences = implode(', ', array_slice($data['prefs'] ?? [], 0, 10));
            if (!empty($data['prefs_autres'])) {
                $preferences .= ($preferences ? ', ' : '') . substr($data['prefs_autres'], 0, 100);
            }

            $dateImmat = !empty($data['date_immat']) ? $data['date_immat'] : date('Y-m-d');
            $couleur = $data['couleur'] ?? 'Non spécifiée';

            $stmt = $this->prepare("
                INSERT INTO vehicules (utilisateur_id, plaque, date_immat, modele, marque, couleur, places, preferences, type_vehicule)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $this->execute($stmt, [
                $this->userId,
                $data['plaque'],
                $dateImmat,
                $data['modele'],
                $data['marque'],
                $couleur,
                (int)$data['places'],
                $preferences,
                $data['type_vehicule']
            ]);

            $vehicleId = $this->lastInsertId();
            LoggerService::info('Vehicle added', ['user_id' => $this->userId, 'vehicle_id' => $vehicleId]);

            return [
                'success' => true,
                'message' => 'Véhicule ajouté avec succès.',
                'vehicule' => [
                    'id' => $vehicleId,
                    'plaque' => $data['plaque'],
                    'modele' => $data['modele'],
                    'marque' => $data['marque'],
                    'couleur' => $couleur,
                    'type_vehicule' => $data['type_vehicule']
                ]
            ];
        } catch (ValidationException $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'errors' => $e->getErrors()];
        } catch (\PDOException $e) {
            LoggerService::error('Vehicle add error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur lors de l\'ajout du véhicule.'];
        }
    }

    public function supprimerVehicule(int $vehiculeId): array {
        try {
            if (!ValidationService::validateInteger($vehiculeId, 1)) {
                return ['success' => false, 'error' => 'ID véhicule invalide.'];
            }

            $stmt = $this->prepare("SELECT id FROM vehicules WHERE id = ? AND utilisateur_id = ?");
            $this->execute($stmt, [$vehiculeId, $this->userId]);
            $vehicule = $this->fetch($stmt);

            if (!$vehicule) {
                LoggerService::security('Unauthorized vehicle deletion attempt', ['user_id' => $this->userId, 'vehicle_id' => $vehiculeId]);
                return ['success' => false, 'error' => 'Véhicule non trouvé.'];
            }

            $stmt = $this->prepare("DELETE FROM vehicules WHERE id = ?");
            $this->execute($stmt, [$vehiculeId]);

            LoggerService::info('Vehicle deleted', ['user_id' => $this->userId, 'vehicle_id' => $vehiculeId]);

            return ['success' => true, 'message' => 'Véhicule supprimé avec succès.'];
        } catch (\PDOException $e) {
            LoggerService::error('Vehicle deletion error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur lors de la suppression.'];
        }
    }
}