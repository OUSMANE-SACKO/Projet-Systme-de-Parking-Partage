<?php
    class AuthenticateUserUseCase {
        private IUserRepository $userRepository;

        public function __construct(IUserRepository $userRepository) {
            $this->userRepository = $userRepository;
        }

        /**
         * @param string
         * @param string
         * @return array
         */
        public function execute(string $email, string $password): array {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'authenticated' => false,
                    'user' => null,
                    'message' => 'Invalid email format.',
                ];
            }

            $foundUser = $this->userRepository->findByEmail($email);

            if ($foundUser === null) {
                return [
                    'authenticated' => false,
                    'user' => null,
                    'message' => 'Invalid credentials.',
                ];
            }

            $pepper = getenv('PEPPER');
            $password_peppered = hash_hmac("sha256", $password, $pepper);

            if (password_verify($password_peppered, $foundUser->getPasswordHash())) {
                return [
                    'authenticated' => true,
                    'user' => $foundUser,
                    'message' => 'Authentication successful.',
                ];
            }

            return [
                'authenticated' => false,
                'user' => null,
                'message' => 'Invalid credentials.',
            ];
        }
    }
?>