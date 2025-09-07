<?php

class ContactManager
{
    private $nom;
    private $email;
    private $message;

    public function __construct(string $nom, string $email, string $message)
    {
        $this->nom = htmlspecialchars(trim($nom));
        $this->email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        $this->message = htmlspecialchars(trim($message));
    }

    public function isValid(): bool
    {
        return !empty($this->nom) && $this->email && !empty($this->message);
    }

    public function send(): bool
    {
        // Tu peux adapter ce bloc pour sauvegarder dans une BDD ou fichier .txt
        $to = 'contact@ecoride.fr';
        $subject = 'Nouveau message de contact';
        $body = "Nom: {$this->nom}\nEmail: {$this->email}\n\nMessage:\n{$this->message}";
        $headers = "From: {$this->email}";

        return mail($to, $subject, $body, $headers);
    }
}