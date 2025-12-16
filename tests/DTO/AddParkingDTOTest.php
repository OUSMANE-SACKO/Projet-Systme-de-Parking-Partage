<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class AddParkingDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new AddParkingDTO('owner123', '123 Main St', 50, 10.5, ' A nice parking ');
        
        $this->assertEquals('owner123', $dto->ownerId);
        $this->assertEquals('123 Main St', $dto->address);
        $this->assertEquals(50, $dto->capacity);
        $this->assertEquals(10.5, $dto->pricePerHour);
        $this->assertEquals('A nice parking', $dto->description);
    }

    public function testFromArray(): void
    {
        $data = [
            'ownerId' => 'owner123',
            'address' => '123 Main St',
            'capacity' => 50,
            'pricePerHour' => 10.5,
            'description' => 'A nice parking'
        ];

        $dto = AddParkingDTO::fromArray($data);

        $this->assertEquals('owner123', $dto->ownerId);
        $this->assertEquals('123 Main St', $dto->address);
        $this->assertEquals(50, $dto->capacity);
        $this->assertEquals(10.5, $dto->pricePerHour);
        $this->assertEquals('A nice parking', $dto->description);
    }

    public function testValidateSuccess(): void
    {
        $dto = new AddParkingDTO('owner123', '123 Main St', 50, 10.5);
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateMissingOwnerId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ownerId requis.');
        
        $dto = new AddParkingDTO('', '123 Main St', 50, 10.5);
        $dto->validate();
    }

    public function testValidateMissingAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Adresse requise.');
        
        $dto = new AddParkingDTO('owner123', '', 50, 10.5);
        $dto->validate();
    }

    public function testValidateInvalidCapacity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacité invalide.');
        
        $dto = new AddParkingDTO('owner123', '123 Main St', 0, 10.5);
        $dto->validate();
    }

    public function testValidateInvalidPrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prix horaire invalide.');
        
        $dto = new AddParkingDTO('owner123', '123 Main St', 50, -1.0);
        $dto->validate();
    }
}
