<?php


use PHPUnit\Framework\TestCase;

class PricingScheduleTest extends TestCase {
    
    public function testPricingScheduleConstruction(): void {
        $time = new DateTime('09:00:00');
        $price = 5.50;
        
        $schedule = new PricingSchedule($time, $price);
        
        $this->assertNotEmpty($schedule->getId());
        $this->assertEquals($time, $schedule->getTime());
        $this->assertEquals($price, $schedule->getPrice());
    }
    
    public function testNegativePriceThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('price must be >= 0');
        
        new PricingSchedule(new DateTime('09:00:00'), -1.0);
    }
    
    public function testZeroPriceIsValid(): void {
        $schedule = new PricingSchedule(new DateTime('09:00:00'), 0.0);
        
        $this->assertEquals(0.0, $schedule->getPrice());
    }
    
    public function testSetters(): void {
        $schedule = new PricingSchedule(new DateTime('09:00:00'), 5.0);
        
        $newTime = new DateTime('10:30:00');
        $newPrice = 7.50;
        
        $schedule->setTime($newTime);
        $schedule->setPrice($newPrice);
        
        $this->assertEquals($newTime, $schedule->getTime());
        $this->assertEquals($newPrice, $schedule->getPrice());
    }
    
    public function testUniqueIds(): void {
        $schedule1 = new PricingSchedule(new DateTime('09:00:00'), 5.0);
        $schedule2 = new PricingSchedule(new DateTime('10:00:00'), 6.0);
        
        $this->assertNotEquals($schedule1->getId(), $schedule2->getId());
    }
    
    public function testTimeComparison(): void {
        $earlySchedule = new PricingSchedule(new DateTime('08:00:00'), 4.0);
        $lateSchedule = new PricingSchedule(new DateTime('18:00:00'), 8.0);
        
        $this->assertLessThan($lateSchedule->getTime(), $earlySchedule->getTime());
    }
}
