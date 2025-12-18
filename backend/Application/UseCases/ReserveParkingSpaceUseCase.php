<?php
class ReserveParkingSpaceUseCase {
    /**
     * @throws InvalidArgumentException Si les paramètres sont invalides
     * @throws RuntimeException Si le parking est plein
     */
    public function execute(Customer $customer, Parking $parking, DateTime $startTime, DateTime $endTime): Reservation {
        if ($endTime <= $startTime) {
            throw new InvalidArgumentException('End time must be after start time.');
        }
        
        if (!$parking->hasAvailableSpace()) {
            throw new RuntimeException('Parking is full.');
        }
        
        $reservation = new Reservation($customer, $parking, $startTime, $endTime);
        
        $parking->addReservation($reservation);
        $customer->addReservation($reservation);
        
        return $reservation;
    }
}
?>