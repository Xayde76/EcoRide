<?php

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

        $accessKey = getenv('WEB3FORMS_KEY');
        if (!$accessKey) {
            error_log('[EcoRide] Contact form: WEB3FORMS_KEY not configured');
            LoggerService::error('Contact form: WEB3FORMS_KEY not configured', []);
            return false;
        }

        $payload = json_encode([
            'access_key' => $accessKey,
            'subject'    => 'Nouveau message de contact EcoRide',
            'from_name'  => 'EcoRide Contact',
            'name'       => $this->nom,
            'email'      => $this->email,
            'message'    => $this->message,
            'botcheck'   => '',
        ]);

        $ch = curl_init('https://api.web3forms.com/submit');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[EcoRide] Web3Forms cURL error: ' . $curlError);
            LoggerService::error('Web3Forms cURL error', ['error' => $curlError]);
            return false;
        }

        $data = json_decode($response, true);
        if (!isset($data['success']) || $data['success'] !== true) {
            error_log('[EcoRide] Web3Forms failed: ' . $response);
            LoggerService::error('Web3Forms send failed', ['response' => $response]);
            return false;
        }

        LoggerService::info('Contact email sent via Web3Forms', ['from' => $this->email]);
        return true;
    }
}
