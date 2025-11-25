<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $this->assertTrue(true);
    }
    
    public function testParkingCreation(): void
    {
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 100);
        
        $this->assertEquals(100, $parking->getCapacity());
        $this->assertEquals($location, $parking->getLocation());
    }
}