<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class ReserveParkingDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new ReserveParkingDTO(' cust123 ', ' park123 ', '2024-01-01', '2024-01-02', ' ABC-123 ');
        
        $this->assertEquals('cust123', $dto->customerId);
        $this->assertEquals('park123', $dto->parkingId);
        $this->assertEquals('2024-01-01', $dto->from);
        $this->assertEquals('2024-01-02', $dto->to);
        $this->assertEquals('ABC-123', $dto->vehiclePlate);
    }

    public function testFromArray(): void
    {
        $data = [
            'customerId' => 'cust123',
            'parkingId' => 'park123',
            'from' => '2024-01-01',
            'to' => '2024-01-02',
            'vehiclePlate' => 'ABC-123'
        ];

        $dto = ReserveParkingDTO::fromArray($data);

        $this->assertEquals('cust123', $dto->customerId);
        $this->assertEquals('park123', $dto->parkingId);
        $this->assertEquals('2024-01-01', $dto->from);
        $this->assertEquals('2024-01-02', $dto->to);
        $this->assertEquals('ABC-123', $dto->vehiclePlate);
    }

    public function testValidateSuccess(): void
    {
        $dto = new ReserveParkingDTO('cust123', 'park123', '2024-01-01', '2024-01-02');
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateMissingCustomerId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client et parking requis.');
        
        $dto = new ReserveParkingDTO('', 'park123', '2024-01-01', '2024-01-02');
        $dto->validate();
    }

    public function testValidateMissingParkingId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client et parking requis.');
        
        $dto = new ReserveParkingDTO('cust123', '', '2024-01-01', '2024-01-02');
        $dto->validate();
    }

    public function testValidateMissingFromDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dates de réservation requises.');
        
        $dto = new ReserveParkingDTO('cust123', 'park123', '', '2024-01-02');
        $dto->validate();
    }

    public function testValidateMissingToDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dates de réservation requises.');
        
        $dto = new ReserveParkingDTO('cust123', 'park123', '2024-01-01', '');
        $dto->validate();
    }
}
