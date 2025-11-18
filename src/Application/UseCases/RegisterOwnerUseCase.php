<?php
    class RegisterOwnerUseCase {
        private IUserRepository $userRepository;

        public function __construct(IUserRepository $userRepository) {
            $this->userRepository = $userRepository;
        }

        /**
         * @param string
         * @param string
         * @param string
         * @param string
         * @return Owner
         */
        public function execute(string $name, string $forename, string $email, string $password): Owner {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format.');
            }

            if ($this->userRepository->existsByEmail($email)) {
                throw new InvalidArgumentException('Email already registered.');
            }

            if (strlen($password) < 8) {
                throw new InvalidArgumentException('Password must be at least 8 characters long.');
            }

            $hashPasswordUseCase = new HashPasswordUseCase();
            $passwordHash = $hashPasswordUseCase->execute($password);

            $owner = new Owner($name, $forename, $email, $passwordHash);
            $this->userRepository->save($owner);

            return $owner;
        }
    }
?>