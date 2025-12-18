<?php

class GetUserSessionsDTO {
    public string $userId;

    public function __construct(string $userId) {
        $this->userId = trim($userId);
    }

    public static function fromArray(array $data): self {
        return new self($data['userId'] ?? '');
    }

    public function validate(): void {
        if ($this->userId === '') {
            throw new InvalidArgumentException('User ID requis.');
        }
    }
}
