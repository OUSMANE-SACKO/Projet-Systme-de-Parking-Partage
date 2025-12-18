<?php


use PHPUnit\Framework\TestCase;

class OwnerTest extends TestCase {
    
    public function testOwnerConstruction(): void {
        $name = 'Smith';
        $forename = 'Jane';
        $email = 'jane.smith@example.com';
        $passwordHash = 'hashed_password_456';
        
        $owner = new Owner($name, $forename, $email, $passwordHash);
        
        $this->assertNull($owner->getId());
        $this->assertEquals($name, $owner->getName());
        $this->assertEquals($forename, $owner->getForename());
        $this->assertEquals($email, $owner->getEmail());
        $this->assertEquals($passwordHash, $owner->getPasswordHash());
        $this->assertEmpty($owner->getParkings());
    }
    
    public function testOwnerInheritsFromUser(): void {
        $owner = new Owner('Brown', 'Bob', 'bob@test.com', 'hash');
        
        $this->assertInstanceOf(User::class, $owner);
        $this->assertEquals('Brown', $owner->getName());
        $this->assertEquals('Bob', $owner->getForename());
    }
    
    public function testAddAndRemoveParking(): void {
        $owner = new Owner('Wilson', 'Alex', 'alex@test.com', 'hash');
        $parking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 30);
        
        $owner->addParking($parking);
        $parkings = $owner->getParkings();
        
        $this->assertCount(1, $parkings);
        $this->assertSame($parking, $parkings[0]);
        
        $result = $owner->removeParking($parking);
        $this->assertTrue($result);
        $this->assertEmpty($owner->getParkings());
    }
    
    public function testRemoveNonExistentParkingReturnsFalse(): void {
        $owner = new Owner('Davis', 'Chris', 'chris@test.com', 'hash');
        $parking = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 15);
        
        $result = $owner->removeParking($parking);
        $this->assertFalse($result);
    }
    
    public function testSetParkingsWithValidArray(): void {
        $owner = new Owner('Johnson', 'Pat', 'pat@test.com', 'hash');
        $parking1 = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 20);
        $parking2 = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 25);
        
        $parkings = [$parking1, $parking2];
        $owner->setParkings($parkings);
        
        $this->assertEquals($parkings, $owner->getParkings());
    }
    
    public function testSetParkingsWithInvalidTypeThrowsException(): void {
        $owner = new Owner('Miller', 'Sam', 'sam@test.com', 'hash');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of Parking');
        
        $owner->setParkings(['not_a_parking']);
    }
    
    public function testMultipleParkings(): void {
        $owner = new Owner('Taylor', 'Jordan', 'jordan@test.com', 'hash');
        $parking1 = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 40);
        $parking2 = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 50);
        $parking3 = new Parking(['latitude' => 0.0, 'longitude' => 0.0], 60);
        
        $owner->addParking($parking1);
        $owner->addParking($parking2);
        $owner->addParking($parking3);
        
        $this->assertCount(3, $owner->getParkings());
        $this->assertContains($parking1, $owner->getParkings());
        $this->assertContains($parking2, $owner->getParkings());
        $this->assertContains($parking3, $owner->getParkings());
    }
}

