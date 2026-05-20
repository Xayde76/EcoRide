<?php

class ContactManager {
    private string $nom;
    private string $email;
    private string $message;

    public function __construct(string $nom, string $email, string $message) {
        $this->nom = ValidationService::sanitizeString($nom);
        $this->email = trim($email);
        $this->message = ValidationService::sanitizeString($message);
    }

    public function isValid(): bool {
        return ValidationService::validateString($this->nom, 1, 255) &&
               ValidationService::validateEmail($this->email) &&
               ValidationService::validateString($this->message, 10, 5000);
    }

    public function send(): bool {
        if (!$this->isValid()) {
            return false;
        }

        try {
            $to = getenv('CONTACT_EMAIL') ?: 'contact@ecoride.fr';
            $subject = 'Nouveau message de contact EcoRide';
            $body = sprintf(
                "Nom: %s\nEmail: %s\nDate: %s\n\nMessage:\n%s",
                $this->nom,
                $this->email,
                date('Y-m-d H:i:s'),
                $this->message
            );

            $headers = [
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $this->email,
                'From: noreply@ecoride.fr'
            ];

            LoggerService::info('Contact form submitted', ['email' => $this->email]);

            return mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (\Exception $e) {
            LoggerService::error('Contact email send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}