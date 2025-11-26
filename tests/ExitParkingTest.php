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
        
        // Grille tarifaire avec plusieurs PricingSchedule
        $schedule1 = new PricingSchedule(new DateTime('2025-11-25 10:00:00'), 2.0);   // 15 min
        $schedule2 = new PricingSchedule(new DateTime('2025-11-25 10:15:00'), 4.5);   // 30 min
        $schedule3 = new PricingSchedule(new DateTime('2025-11-25 10:30:00'), 10.0);  // 60 min
        $parking->addPricingSchedule($schedule1);
        $parking->addPricingSchedule($schedule2);
        $parking->addPricingSchedule($schedule3);
        
        $start = new DateTime('2025-11-25 10:00:00');
        $end = new DateTime('2025-11-25 12:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Sortie en retard de 45 minutes
        $exitTime = new DateTime('2025-11-25 12:45:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Vérification: pénalité = 20 + 10 (1×60min) = 30€
        $this->assertEquals(30.0, $space->getPenaltyAmount());
    }

    public function testExitLateOnLastSchedule(): void
    {
        // Setup
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Charlie', 'Davis', 'charlie@test.com', 'password', '0601020306');
        
        // Plusieurs créneaux horaires (chaque période a ses tranches)
        $schedule1 = new PricingSchedule(new DateTime('2025-11-25 18:00:00'), 3.0);   // 15 min
        $schedule2 = new PricingSchedule(new DateTime('2025-11-25 18:15:00'), 6.0);   // 30 min
        $schedule3 = new PricingSchedule(new DateTime('2025-11-25 18:30:00'), 12.0);  // 60 min
        $parking->addPricingSchedule($schedule1);
        $parking->addPricingSchedule($schedule2);
        $parking->addPricingSchedule($schedule3);
        
        $start = new DateTime('2025-11-25 18:00:00');
        $end = new DateTime('2025-11-25 20:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Sortie en retard de 30 minutes
        $exitTime = new DateTime('2025-11-25 20:30:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Vérification: pénalité = 20 + 6 (1×30min) = 26€
        $this->assertEquals(26.0, $space->getPenaltyAmount());
    }
}
