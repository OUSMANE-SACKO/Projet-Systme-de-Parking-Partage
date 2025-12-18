<?php
/**
 * DTO de réponse pour les statistiques du tableau de bord utilisateur
 */
class DashboardStatsResponseDTO {
    public int $activeReservations;
    public int $totalReservations;
    public ?array $currentSubscription;
    public float $monthlySpending;
    public array $recentReservations;

    public function __construct(
        int $activeReservations,
        int $totalReservations,
        ?array $currentSubscription,
        float $monthlySpending,
        array $recentReservations
    ) {
        $this->activeReservations = $activeReservations;
        $this->totalReservations = $totalReservations;
        $this->currentSubscription = $currentSubscription;
        $this->monthlySpending = $monthlySpending;
        $this->recentReservations = $recentReservations;
    }

    public static function build(
        array $reservations,
        ?Subscription $subscription,
        array $invoices
    ): self {
        $now = new DateTime();
        $startOfMonth = new DateTime('first day of this month 00:00:00');
        
        $activeCount = 0;
        $recentReservations = [];
        
        foreach ($reservations as $reservation) {
            if ($reservation->getEndTime() > $now) {
                $activeCount++;
            }
            
            // Prendre les 5 dernières réservations
            if (count($recentReservations) < 5) {
                $recentReservations[] = ReservationResponseDTO::fromReservation(
                    $reservation,
                    $reservation->getEndTime() > $now ? 'active' : 'completed'
                )->toArray();
            }
        }

        // Calculer les dépenses du mois
        $monthlySpending = 0.0;
        foreach ($invoices as $invoice) {
            if ($invoice->getGeneratedAt() >= $startOfMonth) {
                $monthlySpending += $invoice->getAmount();
            }
        }

        // Abonnement actuel
        $currentSub = null;
        if ($subscription && $subscription->getEndDate() >= $now) {
            $currentSub = SubscriptionResponseDTO::fromSubscription($subscription)->toArray();
        }

        return new self(
            $activeCount,
            count($reservations),
            $currentSub,
            round($monthlySpending, 2),
            $recentReservations
        );
    }

    public function toArray(): array {
        return [
            'activeReservations' => $this->activeReservations,
            'totalReservations' => $this->totalReservations,
            'currentSubscription' => $this->currentSubscription,
            'monthlySpending' => $this->monthlySpending,
            'recentReservations' => $this->recentReservations,
        ];
    }
}
