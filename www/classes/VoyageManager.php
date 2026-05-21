<?php

class VoyageManager extends BaseManager {
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        parent::__construct($pdo);
        $this->userId = $userId;
    }

    public function creerVoyage(array $data): array {
        try {
            $errors = [];

            if (!isset($data['vehicule_id']) || !ValidationService::validateInteger($data['vehicule_id'])) {
                $errors['vehicule_id'] = 'Véhicule invalide.';
            }

            $dateValide = !empty($data['date_depart']) && \DateTime::createFromFormat('Y-m-d', $data['date_depart']) !== false;
            if (!$dateValide) {
                $errors['date_depart'] = 'Date invalide.';
            }

            if (empty($data['heure_depart'])) {
                $errors['heure_depart'] = 'Heure de départ requise.';
            }

            if (!isset($data['depart']) || !ValidationService::validateString($data['depart'], 1, 255)) {
                $errors['depart'] = 'Lieu de départ invalide.';
            }

            if (!isset($data['destination']) || !ValidationService::validateString($data['destination'], 1, 255)) {
                $errors['destination'] = 'Destination invalide.';
            }

            if (!isset($data['prix']) || !ValidationService::validateNumber($data['prix'], 0, 1000)) {
                $errors['prix'] = 'Prix invalide.';
            }

            if (!empty($errors)) {
                return ['success' => false, 'error' => 'Validation failed', 'errors' => $errors];
            }

            $vehiculeId = (int)$data['vehicule_id'];
            $stmt = $this->prepare("SELECT places, type_vehicule FROM vehicules WHERE id = ? AND utilisateur_id = ?");
            $this->execute($stmt, [$vehiculeId, $this->userId]);
            $vehicule = $this->fetch($stmt);

            if (!$vehicule) {
                LoggerService::security('Unauthorized vehicle access attempt', ['user_id' => $this->userId, 'vehicle_id' => $vehiculeId]);
                return ['success' => false, 'error' => 'Véhicule introuvable.'];
            }

            $date  = $data['date_depart'];
            $heure = $data['heure_depart'];

            $this->beginTransaction();

            $heureArrivee = !empty($data['heure_arrivee']) ? $data['heure_arrivee'] : null;

            $stmt = $this->prepare("
                INSERT INTO covoiturage (
                    utilisateur_id, vehicule_id, date_depart, heure_depart,
                    heure_arrivee, lieu_depart, lieu_arrivee, nb_place, prix_personne,
                    statut, type_vehicule
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'disponible', ?)
            ");

            $this->execute($stmt, [
                $this->userId,
                $vehiculeId,
                $date,
                $heure,
                $heureArrivee,
                $data['depart'],
                $data['destination'],
                $vehicule['places'],
                $data['prix'],
                $vehicule['type_vehicule']
            ]);

            $tripId = $this->lastInsertId();

            $this->commit();
            LoggerService::info('Trip created', ['user_id' => $this->userId, 'trip_id' => $tripId]);

            return [
                'success' => true,
                'message' => 'Trajet créé avec succès.',
                'voyage' => [
                    'id' => $tripId,
                    'lieu_depart' => $data['depart'],
                    'lieu_arrivee' => $data['destination'],
                    'date_depart' => $date,
                    'statut' => 'disponible'
                ]
            ];
        } catch (\PDOException $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }
            LoggerService::error('Trip creation error', ['error' => $e->getMessage(), 'user_id' => $this->userId]);
            return ['success' => false, 'error' => 'Erreur serveur lors de la création du trajet.'];
        }
    }

    public function annulerParticipation(int $covoiturageId): array {
        try {
            if (!ValidationService::validateInteger($covoiturageId, 1)) {
                return ['success' => false, 'error' => 'ID de covoiturage invalide.'];
            }

            $stmt = $this->prepare("
                SELECT c.prix_personne
                FROM participation p
                JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
                WHERE p.covoiturage_id = ? AND p.utilisateur_id = ?
            ");
            $this->execute($stmt, [$covoiturageId, $this->userId]);
            $participation = $this->fetch($stmt);

            if (!$participation) {
                return ['success' => false, 'error' => 'Participation non trouvée.'];
            }

            $this->beginTransaction();

            $stmt = $this->prepare("DELETE FROM participation WHERE covoiturage_id = ? AND utilisateur_id = ?");
            $this->execute($stmt, [$covoiturageId, $this->userId]);

            $stmt = $this->prepare("UPDATE covoiturage SET nb_place = nb_place + 1 WHERE covoiturage_id = ?");
            $this->execute($stmt, [$covoiturageId]);

            $stmt = $this->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?");
            $this->execute($stmt, [$participation['prix_personne'], $this->userId]);

            $stmt = $this->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
            $this->execute($stmt, [$this->userId]);
            $newCredits = $this->fetchColumn($stmt);

            $this->commit();

            LoggerService::info('Participation cancelled', ['user_id' => $this->userId, 'trip_id' => $covoiturageId]);

            return ['success' => true, 'message' => 'Participation annulée.', 'new_credits' => $newCredits];
        } catch (\PDOException $e) {
            $this->rollBack();
            LoggerService::error('Participation cancellation error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur lors de l\'annulation.'];
        }
    }
}