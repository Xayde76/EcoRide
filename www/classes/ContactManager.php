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
        if (!$smtpHost) {
            error_log('[EcoRide] Contact form: SMTP_HOST not configured');
            LoggerService::error('Contact form: SMTP not configured', []);
            return false;
        }

        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (!file_exists($autoload)) {
            error_log('[EcoRide] Contact form: vendor/autoload.php not found at ' . $autoload);
            LoggerService::error('Contact form: autoload missing', ['path' => $autoload]);
            return false;
        }

        require_once $autoload;

        try {
            $debugOutput = '';

            $mail = new PHPMailer(true);
            $mail->SMTPDebug  = 3;
            $mail->Debugoutput = function (string $str, int $level) use (&$debugOutput): void {
                $debugOutput .= $str . "\n";
            };
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);
            $mail->Timeout    = 15;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

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
            LoggerService::info('Contact email sent', ['to' => $to]);
            return true;

        } catch (Exception $e) {
            $err = $e->getMessage();
            error_log('[EcoRide] SMTP send failed: ' . $err);
            error_log('[EcoRide] SMTP debug: ' . $debugOutput);
            LoggerService::error('SMTP send failed', ['error' => $err]);
            return false;
        } catch (\Exception $e) {
            error_log('[EcoRide] Contact send error: ' . $e->getMessage());
            LoggerService::error('Contact send error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
