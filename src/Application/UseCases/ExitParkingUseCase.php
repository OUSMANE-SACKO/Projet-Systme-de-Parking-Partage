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
        
        $overtimeSeconds = $exitTime->getTimestamp() - $reservationEnd->getTimestamp();
        $overtimeHours = (int) ceil($overtimeSeconds / 3600);
        
        // Trouver le créneau tarifaire applicable au moment de la fin de réservation
        $applicableSchedule = null;
        foreach ($schedules as $schedule) {
            if ($schedule instanceof PricingSchedule) {
                if ($schedule->getTime() <= $reservationEnd) {
                    $applicableSchedule = $schedule;
                }
            }
        }
        
        if ($applicableSchedule) {
            return $applicableSchedule->getPrice() * $overtimeHours;
        }

        return $overtimeHours; // tarif par défaut
    }
}