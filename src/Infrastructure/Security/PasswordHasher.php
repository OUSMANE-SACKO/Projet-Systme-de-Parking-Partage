<?php
    class PasswordHasher implements IPasswordHasher {
        private string $pepper;

        public function __construct(?string $pepper = null) {
            $this->pepper = $pepper ?? getenv('PEPPER') ?? '';
        }

        public function hash(string $password): string {
            if (trim($password) === '') {
                throw new InvalidArgumentException('Password cannot be empty.');
            }

            $password_peppered = hash_hmac("sha256", $password, $this->pepper);

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hash = password_hash($password_peppered, $algo);

            if ($hash === false) {
                throw new RuntimeException('Password hashing failed.');
            }

            return $hash;
        }

        public function verify(string $password, string $hash): bool {
            $password_peppered = hash_hmac("sha256", $password, $this->pepper);
            return password_verify($password_peppered, $hash);
        }
    }
?>