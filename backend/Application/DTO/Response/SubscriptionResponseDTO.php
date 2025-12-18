<?php
/**
 * DTO de réponse pour un abonnement
 */
class SubscriptionResponseDTO {
    public ?int $id;
    public int $customerId;
    public int $subscriptionTypeId;
    public string $typeName;
    public float $monthlyPrice;
    public int $durationMonths;
    public string $startDate;
    public string $endDate;
    public string $status;
    public ?int $parkingId;
    public ?string $parkingName;

    public function __construct(
        ?int $id,
        int $customerId,
        int $subscriptionTypeId,
        string $typeName,
        float $monthlyPrice,
        int $durationMonths,
        string $startDate,
        string $endDate,
        string $status,
        ?int $parkingId = null,
        ?string $parkingName = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->subscriptionTypeId = $subscriptionTypeId;
        $this->typeName = $typeName;
        $this->monthlyPrice = $monthlyPrice;
        $this->durationMonths = $durationMonths;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->parkingId = $parkingId;
        $this->parkingName = $parkingName;
    }

    public static function fromSubscription(Subscription $subscription): self {
        $type = $subscription->getSubscriptionType();
        $now = new DateTime();
        $endDate = $subscription->getEndDate();
        $status = $endDate >= $now ? 'active' : 'expired';

        return new self(
            $subscription->getId(),
            $subscription->getCustomer()->getId(),
            $type->getId(),
            $type->getName(),
            $type->getMonthlyPrice(),
            $type->getDurationMonths(),
            $subscription->getStartDate()->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $status,
            $subscription->getParking()?->getId(),
            null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'subscriptionTypeId' => $this->subscriptionTypeId,
            'typeName' => $this->typeName,
            'monthlyPrice' => $this->monthlyPrice,
            'durationMonths' => $this->durationMonths,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'status' => $this->status,
            'parkingId' => $this->parkingId,
            'parkingName' => $this->parkingName,
        ];
    }
}
