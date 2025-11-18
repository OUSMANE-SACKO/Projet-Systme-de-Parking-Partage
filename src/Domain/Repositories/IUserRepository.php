<?php
    interface IUserRepository {
        /**
         * @param string $email
         * @return User|null
         */
        public function findByEmail(string $email): ?User;

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
        public function existsByEmail(string $email): bool;
    }
?>