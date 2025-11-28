<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class ParkingSpaceTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john@test.com', 'hash');
        $this->parking = new Parking(['address' => '123 Test St'], 10);
    }
    
    public function testParkingSpaceConstruction(): void {
        $startTime = new DateTime('2024-01-01 10:00:00');
        
        $parkingSpace = new ParkingSpace($this->customer, $startTime, $this->parking);
        
        $this->assertNotEmpty($parkingSpace->getId());
        $this->assertSame($this->customer, $parkingSpace->getCustomer());
        $this->assertEquals($startTime, $parkingSpace->getStartTime());
        $this->assertNull($parkingSpace->getEndTime());
        $this->assertSame($this->parking, $parkingSpace->getParking());
    }
    
    public function testSetters(): void {
        $parkingSpace = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        
        $newCustomer = new Customer('Smith', 'Jane', 'jane@test.com', 'hash2');
        $newStartTime = new DateTime('2024-01-02 09:00:00');
        $newEndTime = new DateTime('2024-01-02 10:00:00');
        $newParking = new Parking(['address' => '456 Other St'], 20);
        
        $parkingSpace->setCustomer($newCustomer);
        $parkingSpace->setStartTime($newStartTime);
        $parkingSpace->setEndTime($newEndTime);
        $parkingSpace->setParking($newParking);
        
        $this->assertSame($newCustomer, $parkingSpace->getCustomer());
        $this->assertEquals($newStartTime, $parkingSpace->getStartTime());
        $this->assertEquals($newEndTime, $parkingSpace->getEndTime());
        $this->assertSame($newParking, $parkingSpace->getParking());
    }
    
    public function testReservationLink(): void {
        $parkingSpace = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        
        $reservation = new Reservation(
            $this->customer,
            $this->parking,
            new DateTime('2024-01-01 10:00:00'),
            new DateTime('2024-01-01 12:00:00')
        );
        
        $parkingSpace->setReservation($reservation);
        
        $this->assertSame($reservation, $parkingSpace->getReservation());
    }
    
    public function testUniqueIds(): void {
        $space1 = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        $space2 = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        
        $this->assertNotEquals($space1->getId(), $space2->getId());
    }
    
    public function testIsOccupiedWhenEndTimeIsNull(): void {
        $parkingSpace = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        
        // No end time means occupied
        $this->assertNull($parkingSpace->getEndTime());
    }
    
    public function testIsNotOccupiedWhenEndTimeIsSet(): void {
        $parkingSpace = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        $parkingSpace->setEndTime(new DateTime());
        
        $this->assertNotNull($parkingSpace->getEndTime());
    }

    public function testPenaltyAmount(): void {
        $parkingSpace = new ParkingSpace($this->customer, new DateTime(), $this->parking);
        
        $this->assertEquals(0.0, $parkingSpace->getPenaltyAmount());
        
        // Test setting valid penalty amount
        $parkingSpace->setPenaltyAmount(25.0);
        $this->assertEquals(25.0, $parkingSpace->getPenaltyAmount());
        
        // Test exception for negative penalty amount
        try {
            $parkingSpace->setPenaltyAmount(-25);
            $this->fail('Expected InvalidArgumentException for negative penalty amount');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('penaltyAmount must be >= 0', $e->getMessage());
        }
    }
}
