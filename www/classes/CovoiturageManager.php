<?php

class CovoiturageManager {
    private PDO $pdo;
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function annulerCovoiturage(int $id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM covoiturage WHERE covoiturage_id = ? AND utilisateur_id = ?");
        $stmt->execute([$id, $this->userId]);
        $covoiturage = $stmt->fetch();

        if (!$covoiturage) {
            return ["success" => false, "error" => "Covoiturage non trouvé ou non autorisé."];
        }

        if ($covoiturage['statut'] !== 'disponible') {
            return ["success" => false, "error" => "Le covoiturage ne peut plus être annulé."];
        }

        $this->pdo->prepare("UPDATE covoiturage SET statut = 'annulé' WHERE covoiturage_id = ?")
                  ->execute([$id]);

        $stmt = $this->pdo->prepare("
            SELECT u.email, u.id 
            FROM participation p 
            JOIN utilisateurs u ON p.utilisateur_id = u.id 
            WHERE p.covoiturage_id = ?
        ");
        $stmt->execute([$id]);
        $participants = $stmt->fetchAll();

        foreach ($participants as $p) {
            $this->pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?")
                      ->execute([$covoiturage['prix_personne'], $p['id']]);
        }

        $this->pdo->prepare("DELETE FROM participation WHERE covoiturage_id = ?")->execute([$id]);

        foreach ($participants as $p) {
            @mail(
                $p['email'],
                "Annulation de covoiturage",
                "Bonjour,\n\nLe covoiturage prévu le " . $covoiturage['date_depart'] . " a été annulé par le conducteur.\n\nMerci de votre compréhension."
            );
        }

        return ["success" => true];
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

        if (!empty($filtres['date'])) {
            $conditions[] = 'DATE(c.date_depart) = ?';
            $params[] = $filtres['date'];
        }

        if (!empty($filtres['ecologique'])) {
            $conditions[] = "c.type_vehicule = 'électrique'";
        }

        if (!empty($filtres['prix_max'])) {
            $conditions[] = 'c.prix_personne <= ?';
            $params[] = $filtres['prix_max'];
        }

        if (!empty($filtres['duree_max'])) {
            $conditions[] = "TIMESTAMPDIFF(MINUTE, c.heure_depart, c.heure_arrivee) <= ?";
            $params[] = $filtres['duree_max'];
        }

        if (!empty($filtres['note_min'])) {
            $conditions[] = "(
                SELECT AVG(note) 
                FROM avis a 
                WHERE a.covoiturage_id = c.covoiturage_id
            ) >= ?";
            $params[] = $filtres['note_min'];
        }

        $query = "
            SELECT c.*, u.nom AS conducteur_nom, v.marque, v.modele, v.preferences, v.couleur
            FROM covoiturage c
            JOIN utilisateurs u ON c.utilisateur_id = u.id
            LEFT JOIN vehicules v ON c.vehicule_id = v.id
        ";

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY c.date_depart ASC';

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function suggereProchaineDate(string $depart, string $destination): ?string {
        $stmt = $this->pdo->prepare("
            SELECT MIN(c.date_depart) as prochaine_date
            FROM covoiturage c
            WHERE c.lieu_depart LIKE ? 
            AND c.lieu_arrivee LIKE ? 
            AND c.statut = 'disponible'
            AND c.date_depart > CURDATE()
        ");
        $stmt->execute(["%$depart%", "%$destination%"]);
        return $stmt->fetchColumn() ?: null;
    }
}