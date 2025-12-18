<?php
/**
 * DTO de réponse générique pour les erreurs
 */
class ErrorResponseDTO {
    public bool $success;
    public string $message;
    public int $code;
    public ?array $errors;

    public function __construct(string $message, int $code = 400, ?array $errors = null) {
        $this->success = false;
        $this->message = $message;
        $this->code = $code;
        $this->errors = $errors;
    }

    public static function notFound(string $resource = 'Resource'): self {
        return new self("$resource non trouvé.", 404);
    }

    public static function unauthorized(string $message = 'Non autorisé.'): self {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Accès refusé.'): self {
        return new self($message, 403);
    }

    public static function validation(array $errors): self {
        return new self('Erreur de validation.', 422, $errors);
    }

    public static function serverError(string $message = 'Erreur interne du serveur.'): self {
        return new self($message, 500);
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'code' => $this->code,
            'errors' => $this->errors,
        ];
    }
}
