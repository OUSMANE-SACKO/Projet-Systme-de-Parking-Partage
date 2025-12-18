<?php

use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    private Reservation $reservation;
    private ParkingSpace $parkingSpace;
    private Subscription $subscription;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john@test.com', 'hash123');
        $this->parking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 10);
        
        // Create test entities
        $this->reservation = new Reservation($this->customer, $this->parking, new DateTime('2024-01-01 10:00'), new DateTime('2024-01-01 12:00'));
        $this->parkingSpace = new ParkingSpace($this->customer, new DateTime('2024-01-01'), $this->parking);
        $type = new SubscriptionType('Monthly', 'Monthly access', 50.0, 1);
        $this->subscription = new Subscription($this->customer, new DateTime('2024-01-01'), new DateTime('2024-02-01'), $type, 1);
    }
    
    /**
     * Test Customer construction and inheritance
     */
    public function testConstructionAndInheritance(): void {
        // Test inheritance and basic properties
        $this->assertInstanceOf(User::class, $this->customer);
        $this->assertInstanceOf(Customer::class, $this->customer);
        $this->assertEquals('Doe', $this->customer->getName());
        $this->assertEquals('John', $this->customer->getForename());
        $this->assertEquals('john@test.com', $this->customer->getEmail());
        $this->assertEquals('hash123', $this->customer->getPasswordHash());
        
        // Test customer-specific properties are empty arrays
        $this->assertEquals([], $this->customer->getReservations());
        $this->assertEquals([], $this->customer->getParkingSpaces());
        $this->assertEquals([], $this->customer->getSubscriptions());
    }
    
    /**
     * Test all reservation management (add, remove, set with validation)
     */
    public function testReservationManagement(): void {
        // Test add and get
        $this->customer->addReservation($this->reservation);
        $this->assertCount(1, $this->customer->getReservations());
        $this->assertSame($this->reservation, $this->customer->getReservations()[0]);
        
        // Test remove (existing and non-existing)
        $this->assertTrue($this->customer->removeReservation($this->reservation));
        $this->assertEmpty($this->customer->getReservations());
        $this->assertFalse($this->customer->removeReservation($this->reservation));
        
        // Test set with valid array and validation
        $this->customer->setReservations([$this->reservation]);
        $this->assertEquals([$this->reservation], $this->customer->getReservations());
        
        // Test validation error
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of Reservation');
        $this->customer->setReservations(['invalid']);
    }
    
    /**
     * Test all parking space management 
     */
    public function testParkingSpaceManagement(): void {
        // Test add and get
        $this->customer->addParkingSpace($this->parkingSpace);
        $this->assertCount(1, $this->customer->getParkingSpaces());
        $this->assertSame($this->parkingSpace, $this->customer->getParkingSpaces()[0]);
        
        // Test remove (existing and non-existing)
        $this->assertTrue($this->customer->removeParkingSpace($this->parkingSpace));
        $this->assertEmpty($this->customer->getParkingSpaces());
        $this->assertFalse($this->customer->removeParkingSpace($this->parkingSpace));
        
        // Test set with validation
        $this->customer->setParkingSpaces([$this->parkingSpace]);
        $this->assertEquals([$this->parkingSpace], $this->customer->getParkingSpaces());
        
        // Test validation error
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of ParkingSpace');
        $this->customer->setParkingSpaces([new stdClass()]);
    }
    
    /**
     * Test all subscription management
     */
    public function testSubscriptionManagement(): void {
        // Test add and get
        $this->customer->addSubscription($this->subscription);
        $this->assertCount(1, $this->customer->getSubscriptions());
        $this->assertSame($this->subscription, $this->customer->getSubscriptions()[0]);
        
        // Test remove (existing and non-existing)
        $this->assertTrue($this->customer->removeSubscription($this->subscription));
        $this->assertEmpty($this->customer->getSubscriptions());
        $this->assertFalse($this->customer->removeSubscription($this->subscription));
        
        // Test set with validation
        $this->customer->setSubscriptions([$this->subscription]);
        $this->assertEquals([$this->subscription], $this->customer->getSubscriptions());
        
        // Test validation error
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of Subscription');
        $this->customer->setSubscriptions([42]);
    }
    
    /**
     * Test array management edge cases and type validation
     */
    public function testArrayManagementEdgeCases(): void {
        // Test multiple additions and array_values behavior
        $this->customer->addReservation($this->reservation);
        $this->customer->addReservation($this->reservation); // Same object twice
        $this->assertCount(2, $this->customer->getReservations());
        
        // Test removing only removes first occurrence
        $this->assertTrue($this->customer->removeReservation($this->reservation));
        $this->assertCount(1, $this->customer->getReservations());
        
        // Test array_values reindexing with setters
        $this->customer->setReservations([5 => $this->reservation, 10 => $this->reservation]);
        $reservations = $this->customer->getReservations();
        $this->assertSame($this->reservation, $reservations[0]);
        $this->assertSame($this->reservation, $reservations[1]);
        
        // Test type validation for arrays
        $this->assertContainsOnlyInstancesOf(Reservation::class, $this->customer->getReservations());
    }
}

