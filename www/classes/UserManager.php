<?php

class UserManager extends BaseManager {
    public function login(string $email, string $password): array {
        try {
            if (!ValidationService::validateEmail($email)) {
                return ['success' => false, 'message' => 'Email invalide.'];
            }

            $stmt = $this->prepare("SELECT id, nom, password, role_id, actif FROM utilisateurs WHERE email = ?");
            $this->execute($stmt, [$email]);
            $utilisateur = $this->fetch($stmt);

            if (!$utilisateur) {
                LoggerService::warning('Login failed - user not found', ['email' => $email]);
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
            }

            if (!$utilisateur['actif']) {
                LoggerService::security('Login attempt - account suspended', ['user_id' => $utilisateur['id']]);
                return ['success' => false, 'message' => 'Votre compte a été suspendu.'];
            }

            if (!password_verify($password, $utilisateur['password'])) {
                LoggerService::warning('Login failed - wrong password', ['user_id' => $utilisateur['id']]);
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
            }

            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_nom'] = $utilisateur['nom'];
            $_SESSION['role_id'] = $utilisateur['role_id'];

            CsrfService::generateToken();
            LoggerService::info('User logged in', ['user_id' => $utilisateur['id']]);

            return [
                'success' => true,
                'message' => 'Connexion réussie.',
                'redirect' => BASE_URL . '/index.php'
            ];
        } catch (\PDOException $e) {
            LoggerService::error('Login database error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur serveur.'];
        }
    }

    public function register(string $nom, string $email, string $password, string $confirm): array {
        try {
            $errors = [];

            if (!ValidationService::validateString($nom, 1, 255)) {
                $errors['nom'] = 'Le nom doit contenir entre 1 et 255 caractères.';
            }

            if (!ValidationService::validateEmail($email)) {
                $errors['email'] = 'Adresse email invalide.';
            }

            if (!ValidationService::validatePassword($password)) {
                $errors['password'] = 'Le mot de passe doit faire au moins 6 caractères.';
            }

            if ($password !== $confirm) {
                $errors['confirm'] = 'Les mots de passe ne correspondent pas.';
            }

            if (!empty($errors)) {
                return ['success' => false, 'message' => 'Validation failed', 'errors' => $errors];
            }

            $check = $this->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $this->execute($check, [$email]);
            if ($this->fetch($check)) {
                return ['success' => false, 'message' => 'Un compte avec cet email existe déjà.'];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->prepare("INSERT INTO utilisateurs (nom, email, password, role_id, actif) VALUES (?, ?, ?, 3, 1)");
            $this->execute($stmt, [$nom, $email, $hashedPassword]);

            $userId = $this->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_nom'] = $nom;
            $_SESSION['role_id'] = 3;

            CsrfService::generateToken();
            LoggerService::info('New user registered', ['user_id' => $userId, 'email' => $email]);

            return [
                'success' => true,
                'message' => 'Inscription réussie.',
                'redirect' => BASE_URL . '/index.php'
            ];
        } catch (\PDOException $e) {
            LoggerService::error('Registration database error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur serveur lors de l\'inscription.'];
        }
    }
}