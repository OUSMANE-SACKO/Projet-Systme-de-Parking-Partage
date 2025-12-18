<?php


use PHPUnit\Framework\TestCase;

class PricingTierTest extends TestCase {
    
    public function testPricingTierConstruction(): void {
        $time = new DateTime('09:00:00');
        $price = 5.50;
        
        $schedule = new PricingTier($time, $price);
        
        
        $this->assertEquals($time, $schedule->getTime());
        $this->assertEquals($price, $schedule->getPrice());
    }
    
    public function testNegativePriceThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('price must be >= 0');
        
        new PricingTier(new DateTime('09:00:00'), -1.0);
    }
    
    public function testZeroPriceIsValid(): void {
        $schedule = new PricingTier(new DateTime('09:00:00'), 0.0);
        
        $this->assertEquals(0.0, $schedule->getPrice());
    }
    
    public function testSetters(): void {
        $schedule = new PricingTier(new DateTime('09:00:00'), 5.0);
        
        $newTime = new DateTime('10:30:00');
        $newPrice = 7.50;
        
        $schedule->setTime($newTime);
        $schedule->setPrice($newPrice);
        
        $this->assertEquals($newTime, $schedule->getTime());
        $this->assertEquals($newPrice, $schedule->getPrice());
    }
    
    public function testUniqueIds(): void {
        $schedule1 = new PricingTier(new DateTime('09:00:00'), 5.0);
        $schedule2 = new PricingTier(new DateTime('10:00:00'), 6.0);
        
        // IDs are null until persisted to database
        $this->assertNull($schedule1->getId());
        $this->assertNull($schedule2->getId());
        
        // Test setId
        $schedule1->setId(1);
        $this->assertEquals(1, $schedule1->getId());
        
        // But the objects are different instances
        $this->assertNotSame($schedule1, $schedule2);
    }
    
    public function testTimeComparison(): void {
        $earlySchedule = new PricingTier(new DateTime('08:00:00'), 4.0);
        $lateSchedule = new PricingTier(new DateTime('18:00:00'), 8.0);
        
        $this->assertLessThan($lateSchedule->getTime(), $earlySchedule->getTime());
    }
}

