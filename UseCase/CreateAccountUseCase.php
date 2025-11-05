<?php
    class CreateAccountUseCase {
        public function execute(string $username, string $password, string $email) : UserAccount {
            return new UserAccount($username, $password, $email);
        }
    }
?>