<?php

class AuthenticateUserDTO {
    public string $email;
    public string $password;

    public function __construct(string $email, string $password) {
        $this->email = trim($email);
        $this->password = $password;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['email'] ?? '',
            $data['password'] ?? ''
        );
    }

    public function validate(): void {
        if ($this->email === '' || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Veuillez entrer un email valide.');
        }
        if (strlen($this->password) < 8) {
            throw new InvalidArgumentException('Veuillez entrer un mot de passe d\'au moins 8 caractères.');
        }
    }
}
