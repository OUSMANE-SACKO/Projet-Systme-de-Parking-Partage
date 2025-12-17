<?php
/**
 * DTO de réponse générique pour les succès
 */
class SuccessResponseDTO {
    public bool $success;
    public string $message;
    public mixed $data;

    public function __construct(string $message, mixed $data = null) {
        $this->success = true;
        $this->message = $message;
        $this->data = $data;
    }

    public static function created(string $resource, mixed $data = null): self {
        return new self("$resource créé avec succès.", $data);
    }

    public static function updated(string $resource, mixed $data = null): self {
        return new self("$resource mis à jour avec succès.", $data);
    }

    public static function deleted(string $resource): self {
        return new self("$resource supprimé avec succès.");
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
