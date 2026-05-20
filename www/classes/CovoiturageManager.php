<?php

class CovoiturageManager extends BaseManager {
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        parent::__construct($pdo);
        $this->userId = $userId;
    }

    public function annulerCovoiturage(int $id): array {
        try {
            if (!ValidationService::validateInteger($id, 1)) {
                return ['success' => false, 'error' => 'ID invalide.'];
            }

            $stmt = $this->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND utilisateur_id = ?");
            $this->execute($stmt, [$id, $this->userId]);
            $covoiturage = $this->fetch($stmt);

            if (!$covoiturage) {
                LoggerService::security('Unauthorized trip cancellation attempt', ['user_id' => $this->userId, 'trip_id' => $id]);
                return ['success' => false, 'error' => 'Trajet non autorisé.'];
            }

            if ($covoiturage['statut'] !== 'disponible') {
                return ['success' => false, 'error' => 'Ce trajet ne peut pas être supprimé.'];
            }

            $this->beginTransaction();

            $stmt = $this->prepare("SELECT p.utilisateur_id FROM participation p WHERE p.covoiturage_id = ?");
            $this->execute($stmt, [$id]);
            $participants = $this->fetchAll($stmt);

            foreach ($participants as $p) {
                $stmt = $this->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?");
                $this->execute($stmt, [$covoiturage['prix_personne'], $p['utilisateur_id']]);
            }

            $stmt = $this->prepare("DELETE FROM participation WHERE covoiturage_id = ?");
            $this->execute($stmt, [$id]);

            $stmt = $this->prepare("DELETE FROM covoiturage WHERE covoiturage_id = ?");
            $this->execute($stmt, [$id]);

            $this->commit();

            LoggerService::info('Trip deleted', ['user_id' => $this->userId, 'trip_id' => $id]);

            return ['success' => true, 'message' => 'Trajet supprimé avec succès.'];
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->rollBack();
            }
            LoggerService::error('Trip cancellation error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erreur lors de l\'annulation.'];
        }
    }

    public function rechercherCovoiturages(array $filtres = []): array {
        $conditions = ["c.statut = 'disponible'", "c.date_depart >= CURDATE()"];
        $params = [];

        if (!empty($filtres['depart'])) {
            $conditions[] = 'c.lieu_depart LIKE ?';
            $params[] = '%' . $filtres['depart'] . '%';
        }

        if (!empty($filtres['destination'])) {
            $conditions[] = 'c.lieu_arrivee LIKE ?';
            $params[] = '%' . $filtres['destination'] . '%';
        }

        if (!empty($filtres['date']) && ValidationService::validateString($filtres['date'], 10, 10)) {
            $conditions[] = 'DATE(c.date_depart) = ?';
            $params[] = $filtres['date'];
        }

        if (!empty($filtres['ecologique'])) {
            $conditions[] = "c.type_vehicule = 'electrique'";
        }

        if (!empty($filtres['prix_max']) && ValidationService::validateNumber($filtres['prix_max'], 0)) {
            $conditions[] = 'c.prix_personne <= ?';
            $params[] = (float)$filtres['prix_max'];
        }

        if (!empty($filtres['duree_max']) && ValidationService::validateInteger($filtres['duree_max'], 0)) {
            $conditions[] = "TIMESTAMPDIFF(MINUTE, c.heure_depart, c.heure_arrivee) <= ?";
            $params[] = (int)$filtres['duree_max'];
        }

        if (!empty($filtres['note_min']) && ValidationService::validateNumber($filtres['note_min'], 0, 5)) {
            $conditions[] = "EXISTS (SELECT 1 FROM avis WHERE covoiturage_id = c.covoiturage_id AND note >= ?)";
            $params[] = (float)$filtres['note_min'];
        }

        $query = "
            SELECT c.*, u.nom AS conducteur_nom, u.id AS conducteur_id, v.marque, v.modele, v.preferences, v.couleur,
                   ROUND(AVG(a.note), 1) AS note_conducteur,
                   COUNT(a.note)         AS nb_avis_conducteur
            FROM covoiturage c
            JOIN utilisateurs u ON c.utilisateur_id = u.id
            LEFT JOIN vehicules v ON c.vehicule_id = v.id
            LEFT JOIN avis a ON a.covoiturage_id IN (
                SELECT covoiturage_id FROM covoiturage c2 WHERE c2.utilisateur_id = u.id
            ) AND a.statut = 'publié'
        ";

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' GROUP BY c.covoiturage_id, u.nom, u.id, v.marque, v.modele, v.preferences, v.couleur';
        $query .= ' ORDER BY c.date_depart ASC LIMIT 100';

        try {
            $stmt = $this->prepare($query);
            $this->execute($stmt, $params);
            return $this->fetchAll($stmt);
        } catch (\PDOException $e) {
            LoggerService::error('Search error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function suggereProchaineDate(string $depart, string $destination): ?string {
        if (!ValidationService::validateString($depart, 1, 255) || !ValidationService::validateString($destination, 1, 255)) {
            return null;
        }

        $stmt = $this->prepare("
            SELECT MIN(c.date_depart) as prochaine_date
            FROM covoiturage c
            WHERE c.lieu_depart LIKE ?
            AND c.lieu_arrivee LIKE ?
            AND c.statut = 'disponible'
            AND c.date_depart > CURDATE()
        ");

        try {
            $this->execute($stmt, ["%$depart%", "%$destination%"]);
            return $this->fetchColumn($stmt) ?: null;
        } catch (\PDOException $e) {
            LoggerService::error('Date suggestion error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}