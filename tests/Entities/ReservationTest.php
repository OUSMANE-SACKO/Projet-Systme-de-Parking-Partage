<?php

use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase {
    private Customer $customer;
    private Parking $parking;
    private DateTime $start;
    private DateTime $end;
    private Reservation $reservation;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john@test.com', 'hash123');
        $this->parking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 10);
        $this->start = new DateTime('2024-01-01 10:00:00');
        $this->end = new DateTime('2024-01-01 12:00:00');
        $this->reservation = new Reservation($this->customer, $this->parking, $this->start, $this->end);
    }
    
    /**
     * Test construction, getters, and date validation
     */
    public function testConstructionAndGetters(): void {
        // Test all getters and null ID (until persisted)
        $this->assertNull($this->reservation->getId());
        $this->assertSame($this->customer, $this->reservation->getCustomer());
        $this->assertSame($this->parking, $this->reservation->getParking());
        $this->assertEquals($this->start, $this->reservation->getStartTime());
        $this->assertEquals($this->end, $this->reservation->getEndTime());
        
        // Test different instances
        $other = new Reservation($this->customer, $this->parking, $this->start, $this->end);
        $this->assertNotSame($this->reservation, $other);
        
        // Test invalid construction (end before start)
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('endTime must be after startTime');
        new Reservation($this->customer, $this->parking, $this->end, $this->start);
    }
    
    /**
     * Test all setters with valid and invalid data
     */
    public function testSetters(): void {
        $newCustomer = new Customer('Smith', 'Jane', 'jane@test.com', 'hash456');
        $newParking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 20);
        
        // Test valid setters
        $this->reservation->setCustomer($newCustomer);
        $this->reservation->setParking($newParking);
        $this->reservation->setStartTime(new DateTime('2024-01-01 09:00:00'));
        $this->reservation->setEndTime(new DateTime('2024-01-01 15:00:00'));
        
        $this->assertSame($newCustomer, $this->reservation->getCustomer());
        $this->assertSame($newParking, $this->reservation->getParking());
        $this->assertEquals(new DateTime('2024-01-01 09:00:00'), $this->reservation->getStartTime());
        $this->assertEquals(new DateTime('2024-01-01 15:00:00'), $this->reservation->getEndTime());
    }
    
    /**
     * Test setter validations for date conflicts
     */
    public function testSetterValidations(): void {
        // Test invalid setStartTime (after endTime)
        try {
            $this->reservation->setStartTime(new DateTime('2024-01-01 13:00:00'));
            $this->fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('startTime must be before endTime', $e->getMessage());
        }
        
        // Test invalid setEndTime (before startTime)
        try {
            $this->reservation->setEndTime(new DateTime('2024-01-01 09:00:00'));
            $this->fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('endTime must be after startTime', $e->getMessage());
        }
    }
    
    /**
     * Test duration calculation and edge cases
     */
    public function testDurationCalculation(): void {
        // Test normal duration (120 minutes)
        $this->assertEquals(120, $this->reservation->getDurationMinutes());
        
        // Test various durations
        $cases = [
            ['10:00:00', '10:00:30', 1],  // 30 seconds rounds up to 1 minute
            ['10:00:00', '10:01:00', 1],  // Exactly 1 minute
            ['10:00:00', '10:01:30', 2],  // 90 seconds rounds up to 2 minutes
            ['10:00:00', '11:30:00', 90], // 90 minutes exactly
            ['10:00:00', '10:00:00', 0],  // Same time = 0 minutes (edge case)
        ];
        
        foreach ($cases as [$startTime, $endTime, $expectedMinutes]) {
            $start = new DateTime("2024-01-01 $startTime");
            $end = new DateTime("2024-01-01 $endTime");
            
            if ($end >= $start) { // Only test valid reservations
                $res = new Reservation($this->customer, $this->parking, $start, $end);
                $this->assertEquals($expectedMinutes, $res->getDurationMinutes());
            }
        }
    }
}

