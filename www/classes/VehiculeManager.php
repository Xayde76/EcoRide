<?php

class VehiculeManager {
  private PDO $pdo;
  private int $userId;

  public function __construct(PDO $pdo, int $userId) {
    $this->pdo = $pdo;
    $this->userId = $userId;
  }

  public function ajouterVehicule(array $data): array {
    $typesValid = ['essence', 'diesel', 'électrique', 'hybride'];

    if (!in_array($data['type_vehicule'], $typesValid)) {
      throw new InvalidArgumentException("Type de véhicule invalide.");
    }

    $preferences = implode(', ', $data['prefs'] ?? []);
    if (!empty(trim($data['prefs_autres'] ?? ''))) {
      $preferences .= ($preferences ? ', ' : '') . trim($data['prefs_autres']);
    }

    $stmt = $this->pdo->prepare("
      INSERT INTO vehicules (utilisateur_id, plaque, date_immat, modele, marque, couleur, places, preferences, type_vehicule)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
      $this->userId,
      $data['plaque'],
      $data['date_immat'],
      $data['modele'],
      $data['marque'],
      $data['couleur'],
      $data['places'],
      $preferences,
      $data['type_vehicule']
    ]);

    return [
      'id' => $this->pdo->lastInsertId(),
      'plaque' => $data['plaque'],
      'modele' => $data['modele'],
      'marque' => $data['marque'],
      'couleur' => $data['couleur'],
      'type_vehicule' => $data['type_vehicule']
    ];
  }

  public function supprimerVehicule(int $vehiculeId): array {
    // Vérifie la propriété du véhicule
    $stmt = $this->pdo->prepare("SELECT * FROM vehicules WHERE id = ? AND utilisateur_id = ?");
    $stmt->execute([$vehiculeId, $this->userId]);
    $vehicule = $stmt->fetch();

    if (!$vehicule) {
      http_response_code(403);
      return ['success' => false, 'error' => 'Véhicule non trouvé ou non autorisé.'];
    }

    // Covoiturages liés (au cas où ON DELETE CASCADE n’est pas configuré)
    $stmt = $this->pdo->prepare("SELECT covoiturage_id FROM covoiturage WHERE vehicule_id = ?");
    $stmt->execute([$vehiculeId]);
    $voyageIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Suppression
    $stmt = $this->pdo->prepare("DELETE FROM vehicules WHERE id = ?");
    $stmt->execute([$vehiculeId]);

    return ['success' => true, 'deletedVoyages' => $voyageIds];
  }
}