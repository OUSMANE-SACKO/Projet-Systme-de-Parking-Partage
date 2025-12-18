<?php

class RegisterOwnerDTO {
    public string $name;
    public string $forename;
    public string $email;
    public string $password;

    public function __construct(string $name, string $forename, string $email, string $password) {
        $this->name = trim($name);
        $this->forename = trim($forename);
        $this->email = trim($email);
        $this->password = $password;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['name'] ?? '',
            $data['forename'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? ''
        );
    }

    public function validate(): void {
        if ($this->name === '' || $this->forename === '' || $this->email === '' || $this->password === '') {
            throw new InvalidArgumentException('Tous les champs sont requis.');
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email invalide.');
        }
        if (strlen($this->password) < 6) {
            throw new InvalidArgumentException('Le mot de passe doit contenir au moins 6 caractères.');
        }
    }
}
