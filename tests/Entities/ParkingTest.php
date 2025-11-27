<?php

use PHPUnit\Framework\TestCase;

class ParkingTest extends TestCase {
    private Parking $parking;
    
    protected function setUp(): void {
        $this->parking = new Parking(['address' => '123 Main St', 'city' => 'TestCity'], 50);
    }
    
    public function testConstruction(): void {
        $location = ['address' => '456 Test Ave'];
        $parking = new Parking($location, 100);
        
        $this->assertNotEmpty($parking->getId());
        $this->assertEquals($location, $parking->getLocation());
        $this->assertEquals(100, $parking->getCapacity());
        $this->assertEmpty($parking->getPricingSchedules());
        $this->assertEmpty($parking->getReservations());
        $this->assertEmpty($parking->getParkingSpaces());
        
        $this->expectException(InvalidArgumentException::class);
        new Parking(['address' => 'Test'], -5);
        
        $this->parking->setCapacity(200);
        $this->assertEquals(200, $this->parking->getCapacity());
        
        $this->expectException(InvalidArgumentException::class);
        $this->parking->setCapacity(-10);
    }
    
    public function testPricingSchedules(): void {
        $schedule1 = new PricingSchedule(new DateTime('09:00'), 5.0);
        $schedule2 = new PricingSchedule(new DateTime('17:00'), 8.0);
        
        $this->parking->addPricingSchedule($schedule1);
        $this->assertCount(1, $this->parking->getPricingSchedules());
        $this->assertSame($schedule1, $this->parking->getPricingSchedules()[0]);
        
        $this->parking->setPricingSchedules([$schedule1, $schedule2]);
        $this->assertCount(2, $this->parking->getPricingSchedules());
        
        $this->assertTrue($this->parking->removePricingSchedule($schedule1));
        $this->assertCount(1, $this->parking->getPricingSchedules());
        $this->assertFalse($this->parking->removePricingSchedule($schedule1));
        
        $this->expectException(InvalidArgumentException::class);
        $this->parking->setPricingSchedules(['invalid']);
    }
    
    public function testReservations(): void {
        $customer = new Customer('Doe', 'John', 'john@test.com', 'hash');
        $reservation = new Reservation($customer, $this->parking, new DateTime('2024-01-01 10:00'), new DateTime('2024-01-01 12:00'));
        
        $this->parking->addReservation($reservation);
        $this->assertCount(1, $this->parking->getReservations());
        $this->assertSame($reservation, $this->parking->getReservations()[0]);
        
        $this->assertTrue($this->parking->removeReservation($reservation));
        $this->assertEmpty($this->parking->getReservations());
        $this->assertFalse($this->parking->removeReservation($reservation));
    }
    
    public function testParkingSpaces(): void {
        $customer = new Customer('Smith', 'Jane', 'jane@test.com', 'hash');
        $space1 = new ParkingSpace($customer, new DateTime(), $this->parking);
        $space2 = new ParkingSpace($customer, new DateTime('2024-01-01 10:00'), $this->parking);
        $space2->setEndTime(new DateTime('2024-01-01 11:00')); // Free space
        
        $this->parking->addParkingSpace($space1); // Occupied (no endTime)
        $this->parking->addParkingSpace($space2); // Free (has endTime)
        
        $this->assertCount(2, $this->parking->getParkingSpaces());
        $this->assertEquals(1, $this->parking->getOccupiedSpacesCount());
        
        $this->assertTrue($this->parking->removeParkingSpace($space1));
        $this->assertCount(1, $this->parking->getParkingSpaces());
        $this->assertFalse($this->parking->removeParkingSpace($space1));
    }
    
    public function testSubscriptionTypes(): void {
        $type = new SubscriptionType('Monthly', 'Monthly access', 50.0, 1);
        
        $this->parking->addSubscriptionType($type);
        $this->assertCount(1, $this->parking->getSubscriptions());
        
        $this->assertTrue($this->parking->removeSubscriptionType($type));
        $this->assertEmpty($this->parking->getSubscriptions());
        $this->assertFalse($this->parking->removeSubscriptionType($type));
    }
}
