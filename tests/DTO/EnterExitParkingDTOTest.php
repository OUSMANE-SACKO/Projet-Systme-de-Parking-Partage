<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class EnterExitParkingDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new EnterExitParkingDTO(' parking123 ', ' ABC-123 ', '2024-01-01 10:00:00');
        
        $this->assertEquals('parking123', $dto->parkingId);
        $this->assertEquals('ABC-123', $dto->vehiclePlate);
        $this->assertEquals('2024-01-01 10:00:00', $dto->timestamp);
    }

    public function testFromArray(): void
    {
        $data = [
            'parkingId' => 'parking123',
            'vehiclePlate' => 'ABC-123',
            'timestamp' => '2024-01-01 10:00:00'
        ];

        $dto = EnterExitParkingDTO::fromArray($data);

        $this->assertEquals('parking123', $dto->parkingId);
        $this->assertEquals('ABC-123', $dto->vehiclePlate);
        $this->assertEquals('2024-01-01 10:00:00', $dto->timestamp);
    }

    public function testValidateSuccess(): void
    {
        $dto = new EnterExitParkingDTO('parking123', 'ABC-123');
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateMissingParkingId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parking et plaque requis.');
        
        $dto = new EnterExitParkingDTO('', 'ABC-123');
        $dto->validate();
    }

    public function testValidateMissingVehiclePlate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parking et plaque requis.');
        
        $dto = new EnterExitParkingDTO('parking123', '');
        $dto->validate();
    }
}
