<?php
/**
 * DTO de réponse pour l'authentification utilisateur
 */
class AuthResponseDTO {
    public bool $authenticated;
    public ?array $user;
    public string $message;
    public ?string $token;

    public function __construct(bool $authenticated, ?array $user, string $message, ?string $token = null) {
        $this->authenticated = $authenticated;
        $this->user = $user;
        $this->message = $message;
        $this->token = $token;
    }

    public static function success(User $user, string $token): self {
        return new self(
            true,
            [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'forename' => $user->getForename(),
            ],
            'Authentification réussie.',
            $token
        );
    }

    public static function failure(string $message): self {
        return new self(false, null, $message, null);
    }

    public function toArray(): array {
        return [
            'authenticated' => $this->authenticated,
            'user' => $this->user,
            'message' => $this->message,
            'token' => $this->token,
        ];
    }
}
