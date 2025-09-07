<?php

class UserManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $password): array {
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs.'
            ];
        }

        // On récupère aussi le champ 'actif'
        $stmt = $this->pdo->prepare("SELECT id, nom, password, role_id, actif FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur) {
            if (!$utilisateur['actif']) {
                return [
                    'success' => false,
                    'message' => 'Votre compte a été suspendu.'
                ];
            }

            if (password_verify($password, $utilisateur['password'])) {
                $_SESSION['user_id'] = $utilisateur['id'];
                $_SESSION['user_nom'] = $utilisateur['nom'];
                $_SESSION['role_id'] = $utilisateur['role_id'];

                return [
                    'success' => true,
                    'redirect' => BASE_URL . '/index.php'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Email ou mot de passe incorrect.'
        ];
    }

    public function register(string $nom, string $email, string $password, string $confirm): array {
        if (empty($nom) || empty($email) || empty($password) || empty($confirm)) {
            return [
                'success' => false,
                'message' => "Tous les champs sont requis."
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => "Adresse email invalide."
            ];
        }

        if ($password !== $confirm) {
            return [
                'success' => false,
                'message' => "Les mots de passe ne correspondent pas."
            ];
        }

        $check = $this->pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return [
                'success' => false,
                'message' => "Un compte avec cet email existe déjà."
            ];
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertion avec role_id = 3 (utilisateur standard)
            $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (nom, email, password, role_id) VALUES (?, ?, ?, 3)");
            $stmt->execute([$nom, $email, $hashedPassword]);

            // Récupération des infos pour session
            $userId = $this->pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_nom'] = $nom;

            // Récupération du role_id depuis la base (au cas où la valeur serait modifiée plus tard)
            $stmtRole = $this->pdo->prepare("SELECT role_id FROM utilisateurs WHERE id = ?");
            $stmtRole->execute([$userId]);
            $_SESSION['role_id'] = (int)$stmtRole->fetchColumn();

            return [
                'success' => true,
                'message' => "Inscription et connexion réussies.",
                'redirect' => BASE_URL . '/index.php'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => "Erreur serveur : " . $e->getMessage()
            ];
        }
    }
}