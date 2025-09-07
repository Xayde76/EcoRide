<?php
class VoyageManager {
    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function creerVoyage(array $data): array {
        if (strtotime($data['date_depart']) <= time()) {
            return ["success" => false, "error" => "Veuillez choisir une date future."];
        }

        $date = date('Y-m-d', strtotime($data['date_depart']));
        $heure = date('H:i:s', strtotime($data['date_depart']));

        $stmt = $this->pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
        $stmt->execute([$data['vehicule_id']]);
        $vehicule = $stmt->fetch();

        if (!$vehicule) {
            return ["success" => false, "error" => "Véhicule introuvable."];
        }

        $nbPlaces = $vehicule['places'];
        $type = $vehicule['type_vehicule'];

        if (!$type) {
            return ["success" => false, "error" => "Type de véhicule manquant."];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO covoiturage (
                utilisateur_id, vehicule_id, date_depart, heure_depart,
                date_arrivee, heure_arrivee,
                lieu_depart, lieu_arrivee, nb_place, prix_personne,
                statut, type_vehicule
            ) VALUES (?, ?, ?, ?, NULL, NULL, ?, ?, ?, ?, 'disponible', ?)
        ");

        $stmt->execute([
            $this->userId,
            $data['vehicule_id'],
            $date,
            $heure,
            $data['depart'],
            $data['destination'],
            $nbPlaces,
            $data['prix'],
            $type
        ]);

        return [
            "success" => true,
            "voyage" => [
                "id" => $this->pdo->lastInsertId(),
                "lieu_depart" => $data['depart'],
                "lieu_arrivee" => $data['destination'],
                "date_depart" => $date,
                "statut" => 'disponible'
            ]
        ];
    }

    public function annulerParticipation(int $covoiturageId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM participation WHERE covoiturage_id = ? AND utilisateur_id = ?");
        $stmt->execute([$covoiturageId, $this->userId]);
        $participation = $stmt->fetch();

        if (!$participation) {
            return ["success" => false, "error" => "Aucune participation trouvée."];
        }

        $this->pdo->prepare("DELETE FROM participation WHERE covoiturage_id = ? AND utilisateur_id = ?")
                  ->execute([$covoiturageId, $this->userId]);

        $stmtPrix = $this->pdo->prepare("SELECT prix_personne FROM covoiturage WHERE covoiturage_id = ?");
        $stmtPrix->execute([$covoiturageId]);
        $prix = $stmtPrix->fetchColumn();

        if ($prix !== false) {
            $this->pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?")
                      ->execute([$prix, $this->userId]);
        }

        return ["success" => true];
    }
}