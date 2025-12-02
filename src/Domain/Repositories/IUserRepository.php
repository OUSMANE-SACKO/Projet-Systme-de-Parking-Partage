<?php
    interface IUserRepository {
        /**
         * @param string $email
         * @return User|null
         */
        public function findByEmail(string $email): ?User;

        /**
         * @return User[]
         */
        public function findAll(): array;

        /**
         * @param User $user
         * @return void
         */
        public function save(User $user): void;

        /**
         * @param string $id
         * @return User|null
         */
        public function findById(string $id): ?User;

        /**
         * @param string $email
         * @return bool
         */
        // ça aurrait pu aussi s'appeler findByEmail pour rester cohérent avec le nom de la méthode
        public function existsByEmail(string $email): bool;  
    }
?>