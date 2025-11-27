<?php
use PHPUnit\Framework\TestCase;

class ExitParkingTest extends TestCase
{
    /**
     * Test exit on time - no penalty
     */
    public function testExitOnTime(): void
    {
        // Setup
        $location = ['address' => '123 Test St', 'latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Alice', 'Smith', 'alice@test.com', 'password_hash');
        
        $start = new DateTime('2025-11-25 10:00:00');
        $end = new DateTime('2025-11-25 12:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Exit on time (before reservation end)
        $exitTime = new DateTime('2025-11-25 11:45:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Verification: no penalty
        $this->assertEquals(0.0, $space->getPenaltyAmount());
        $this->assertEquals($exitTime, $space->getEndTime());
    }

    /**
     * Test exit late with penalty calculation
     */
    public function testExitLate(): void
    {
        // Setup
        $location = ['address' => '456 Test Ave', 'latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Bob', 'Brown', 'bob@test.com', 'password_hash');
        
        // Pricing schedule: 8€/hour starting at 10h
        $schedule = new PricingSchedule(new DateTime('2025-11-25 10:00:00'), 8.0);
        $parking->addPricingSchedule($schedule);
        
        $start = new DateTime('2025-11-25 10:00:00');
        $end = new DateTime('2025-11-25 12:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Exit 2 hours late (14h instead of 12h)
        $exitTime = new DateTime('2025-11-25 14:00:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Verification: penalty = base(20) + overtime(8*2) = 36€
        $this->assertEquals(36.0, $space->getPenaltyAmount());
        $this->assertEquals($exitTime, $space->getEndTime());
    }

    /**
     * Test exit late on last pricing schedule
     */
    public function testExitLateOnLastSchedule(): void
    {
        // Setup
        $location = ['address' => '789 Test Blvd', 'latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 10);
        $customer = new Customer('Charlie', 'Davis', 'charlie@test.com', 'password_hash');
        
        // Multiple pricing schedules
        $schedule1 = new PricingSchedule(new DateTime('2025-11-25 08:00:00'), 4.0);
        $schedule2 = new PricingSchedule(new DateTime('2025-11-25 14:00:00'), 6.0);
        $schedule3 = new PricingSchedule(new DateTime('2025-11-25 18:00:00'), 10.0);
        $parking->addPricingSchedule($schedule1);
        $parking->addPricingSchedule($schedule2);
        $parking->addPricingSchedule($schedule3);
        
        $start = new DateTime('2025-11-25 18:00:00');
        $end = new DateTime('2025-11-25 20:00:00');
        $reservation = new Reservation($customer, $parking, $start, $end);
        
        $space = new ParkingSpace($customer, $start, $parking);
        $space->setReservation($reservation);
        
        // Exit 2 hours late on last schedule (22h instead of 20h)
        $exitTime = new DateTime('2025-11-25 22:00:00');
        
        $useCase = new ExitParkingUseCase();
        $useCase->execute($space, $exitTime);
        
        // Verification: penalty = base(20) + overtime(10*2) = 40€
        $this->assertEquals(40.0, $space->getPenaltyAmount());
        $this->assertEquals($exitTime, $space->getEndTime());
    }
}
