<?php
    class User {
        private string $id;
        private string $name;
        private string $forename;
        private string $email;
        private string $passwordHash;

        public function __construct(string $id, string $name, string $forename, string $email, string $passwordHash) {
            $this->id = $id;
            $this->name = $name;
            $this->forename = $forename;
            $this->email = $email;
            $this->passwordHash = $passwordHash;
        }

        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getName() : string {
            return $this->name;
        }

        public function getForename() : string {
            return $this->forename;
        }

        public function getEmail() : string {
            return $this->email;
        }

        //setters
        public function setName(string $name) : void {
            $this->name = $name;
        }

        public function setForename(string $forename) : void {
            $this->forename = $forename;
        }

        public function setEmail(string $email) : void {
            $this->email = $email;
        }

        public function setPasswordHash(string $passwordHash) : void {
            $this->passwordHash = $passwordHash;
        }
    }
?>