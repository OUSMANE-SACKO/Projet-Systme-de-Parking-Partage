<?php

    class HashPasswordUseCase {
        public function execute(string $password): string {
            if (trim($password) === '') {
                throw new InvalidArgumentException('Password cannot be empty.');
            }

            $pepper = getenv('PEPPER');
            
            $password_peppered = hash_hmac("sha256", $password, $pepper);

            $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hash = password_hash($password_peppered, $algo);

            if ($hash === false) {
                throw new RuntimeException('Password hashing failed.');
            }

            return $hash;
        }

        
        public function verify(string $password, string $hash): bool {
            $pepper = getenv('PEPPER');
            $password_peppered = hash_hmac("sha256", $password, $pepper);
            return password_verify($password_peppered, $hash);
        }
    }
?>
