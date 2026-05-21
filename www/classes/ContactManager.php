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

        if (!getenv('SMTP_HOST')) {
            LoggerService::error('Contact form: SMTP not configured', []);
            return false;
        }

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
            $mail->Timeout    = 10;
            $mail->CharSet    = 'UTF-8';

            $from = getenv('SMTP_FROM') ?: getenv('SMTP_USER');
            $to   = getenv('CONTACT_EMAIL') ?: getenv('SMTP_USER');

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
            LoggerService::info('Contact email sent', ['from' => $this->email]);
            return true;

        } catch (Exception $e) {
            LoggerService::error('SMTP send failed', ['error' => $e->getMessage()]);
            return false;
        } catch (\Exception $e) {
            LoggerService::error('Contact send error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
