<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactManager {
    private string $nom;
    private string $email;
    private string $message;

    public function __construct(string $nom, string $email, string $message) {
        $this->nom     = ValidationService::sanitizeString($nom);
        $this->email   = trim($email);
        $this->message = ValidationService::sanitizeString($message);
    }

    public function isValid(): bool {
        return ValidationService::validateString($this->nom, 1, 255) &&
               ValidationService::validateEmail($this->email) &&
               ValidationService::validateString($this->message, 2, 5000);
    }

    public function send(): bool {
        if (!$this->isValid()) {
            return false;
        }

        $smtpHost = getenv('SMTP_HOST');

        // Fallback vers mail() si SMTP non configuré
        if (!$smtpHost) {
            return $this->sendWithMail();
        }

        return $this->sendWithSmtp();
    }

    private function sendWithSmtp(): bool {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);
            $mail->CharSet    = 'UTF-8';

            $from = getenv('SMTP_FROM') ?: 'noreply@ecoride.fr';
            $to   = getenv('CONTACT_EMAIL') ?: 'contact@ecoride.fr';

            $mail->setFrom($from, 'EcoRide');
            $mail->addAddress($to);
            $mail->addReplyTo($this->email, $this->nom);

            $mail->Subject = 'Nouveau message de contact EcoRide';
            $mail->Body    = sprintf(
                "Nom : %s\nEmail : %s\nDate : %s\n\nMessage :\n%s",
                $this->nom,
                $this->email,
                date('d/m/Y H:i'),
                $this->message
            );

            $mail->send();
            LoggerService::info('Contact email sent via SMTP', ['to' => $this->email]);
            return true;
        } catch (Exception $e) {
            LoggerService::error('SMTP send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendWithMail(): bool {
        try {
            $to      = getenv('CONTACT_EMAIL') ?: 'contact@ecoride.fr';
            $subject = 'Nouveau message de contact EcoRide';
            $body    = sprintf(
                "Nom : %s\nEmail : %s\nDate : %s\n\nMessage :\n%s",
                $this->nom, $this->email, date('d/m/Y H:i'), $this->message
            );
            $headers = implode("\r\n", [
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $this->email,
                'From: noreply@ecoride.fr',
            ]);
            LoggerService::info('Contact form submitted', ['email' => $this->email]);
            return mail($to, $subject, $body, $headers);
        } catch (\Exception $e) {
            LoggerService::error('mail() send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
