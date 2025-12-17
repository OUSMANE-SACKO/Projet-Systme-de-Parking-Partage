<?php

class GetUserSubscriptionsDTO {
    public int $userId;

    public function __construct(int $userId) {
        $this->userId = $userId;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['userId'] ?? $data['customerId'] ?? 0
        );
    }
}
