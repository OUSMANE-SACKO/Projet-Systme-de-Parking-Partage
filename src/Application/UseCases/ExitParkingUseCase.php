<?php
class ExitParkingUseCase {
    private const PENALTY_BASE = 20.0;

    public function execute(ParkingSpace $parkingSpace, DateTime $exitTime): void {
        $parkingSpace->setEndTime($exitTime);

        $reservation = $parkingSpace->getReservation();

        if ($exitTime > $reservation->getEndTime()) {
            $parking = $parkingSpace->getParking();
            $overtimeCost = $this->getHourlyRate($parking, $reservation->getEndTime(), $exitTime);
            $parkingSpace->setPenaltyAmount(self::PENALTY_BASE + $overtimeCost);
        } else {
            $parkingSpace->setPenaltyAmount(0.0);
        }
    }

    private function getHourlyRate(Parking $parking, DateTime $reservationEnd, DateTime $exitTime): float {
        $schedules = $parking->getPricingSchedules();
        
        $overtimeMinutes = (int) ceil(($exitTime->getTimestamp() - $reservationEnd->getTimestamp()) / 60);
        
        if (empty($schedules)) {
            return $overtimeMinutes * 0.1; // tarif par défaut
        }

        // Trier les schedules par time
        $sortedSchedules = [];
        foreach ($schedules as $schedule) {
            if ($schedule instanceof PricingSchedule) {
                $sortedSchedules[] = $schedule;
            }
        }
        
        usort($sortedSchedules, function($a, $b) {
            return $a->getTime() <=> $b->getTime();
        });
        
        // Créer les tranches : chaque schedule représente une durée croissante
        // schedule 1 = 15 min, schedule 2 = 30 min, schedule 3 = 60 min, etc.
        $tiers = [];
        $duration = 15; // commence à 15 minutes
        foreach ($sortedSchedules as $schedule) {
            $tiers[$duration] = $schedule->getPrice();
            $duration *= 2; // double à chaque fois: 15, 30, 60, 120...
        }
        
        if (empty($tiers)) {
            return $sortedSchedules[0]->getPrice() * ceil($overtimeMinutes / 60);
        }

        // Trier les tranches par durée croissante
        ksort($tiers);
        
        // Trouver la tranche immédiatement supérieure ou égale
        $selectedTier = null;
        $selectedMinutes = 0;
        
        foreach ($tiers as $minutes => $price) {
            if ($overtimeMinutes <= $minutes) {
                $selectedTier = $price;
                $selectedMinutes = $minutes;
                break;
            }
        }
        
        // Si aucune tranche ne convient, utiliser la plus grande
        if ($selectedTier === null) {
            krsort($tiers);
            foreach ($tiers as $minutes => $price) {
                $selectedTier = $price;
                $selectedMinutes = $minutes;
                break;
            }
        }
        
        // Calculer le nombre de tranches nécessaires
        if ($selectedMinutes > 0) {
            $neededTiers = (int) ceil($overtimeMinutes / $selectedMinutes);
            return $selectedTier * $neededTiers;
        }
        
        return 0.0;
    }
}