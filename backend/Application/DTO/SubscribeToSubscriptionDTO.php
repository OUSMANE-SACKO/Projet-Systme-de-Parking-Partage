<?php

class SubscribeToSubscriptionDTO {
    public string $customerId;
    public string $subscriptionTypeId;
    public ?string $paymentMethodId;

    public function __construct(string $customerId, string $subscriptionTypeId, ?string $paymentMethodId = null) {
        $this->customerId = trim($customerId);
        $this->subscriptionTypeId = trim($subscriptionTypeId);
        $this->paymentMethodId = $paymentMethodId ? trim($paymentMethodId) : null;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['customerId'] ?? '',
            $data['subscriptionTypeId'] ?? '',
            $data['paymentMethodId'] ?? null
        );
    }

    public function validate(): void {
        if ($this->customerId === '' || $this->subscriptionTypeId === '') {
            throw new InvalidArgumentException('Client et type d\'abonnement requis.');
        }
    }
}
