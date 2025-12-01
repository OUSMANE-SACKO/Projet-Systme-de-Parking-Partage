<?php
use PHPUnit\Framework\TestCase;

class ExitParkingTest extends TestCase
{
    public function testExitOnTime(): void
    {
        // Setup
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Alice', 'Smith', 'alice@test.com', 'password', '0601020304');
        
        $start = new DateTime('2025-11-25 10:00:00');
        $end = new DateTime('2025-11-25 12:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Sortie à l'heure
        $exitTime = new DateTime('2025-11-25 11:45:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Vérification: pas de pénalité
        $this->assertEquals(0.0, $space->getPenaltyAmount());
    }

    public function testExitLate(): void
    {
        // Setup
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Bob', 'Brown', 'bob@test.com', 'password', '0601020305');
        
        // Grille tarifaire: 8€/heure à partir de 10h
        $tier = new PricingSchedule(new DateTime('2025-11-25 10:00:00'), 8.0);
        $parking->addPricingSchedule($tier);
        
        $start = new DateTime('2025-11-25 10:00:00');
        $end = new DateTime('2025-11-25 12:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Sortie en retard de 2 heures
        $exitTime = new DateTime('2025-11-25 14:00:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        $this->assertEquals(36.0, $space->getPenaltyAmount());
    }

    public function testExitLateOnLastSchedule(): void
    {
        // Setup
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Charlie', 'Davis', 'charlie@test.com', 'password', '0601020306');
        
        // Plusieurs créneaux horaires
        $tier1 = new PricingSchedule(new DateTime('2025-11-25 08:00:00'), 4.0);
        $tier2 = new PricingSchedule(new DateTime('2025-11-25 14:00:00'), 6.0);
        $tier3 = new PricingSchedule(new DateTime('2025-11-25 18:00:00'), 10.0);
        $parking->addPricingSchedule($tier1);
        $parking->addPricingSchedule($tier2);
        $parking->addPricingSchedule($tier3);
        
        $start = new DateTime('2025-11-25 18:00:00');
        $end = new DateTime('2025-11-25 20:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Sortie en retard sur le dernier créneau (22h au lieu de 20h)
        $exitTime = new DateTime('2025-11-25 22:00:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Vérification: pénalité = 20 + (10 * 2) = 40€
        $this->assertEquals(40.0, $space->getPenaltyAmount());
    }
}
