<?php

class RegisterCustomerDTO {
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

    /**
     * Basic validation for required fields and simple formats.
     * Throws InvalidArgumentException on first error.
     */
    public function validate(): void {
        if ($this->name === '') {
            throw new InvalidArgumentException('Name is required.');
        }
        if ($this->forename === '') {
            throw new InvalidArgumentException('Forename is required.');
        }
        if ($this->email === '' || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email is required.');
        }
        if (strlen($this->password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }
    }
}
