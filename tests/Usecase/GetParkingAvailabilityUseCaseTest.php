<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetParkingAvailabilityUseCaseTest extends TestCase
{
    private GetParkingAvailabilityUseCase $getParkingAvailabilityUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->getParkingAvailabilityUseCase = new GetParkingAvailabilityUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteWithNoOccupiedOrReservedSpaces(): void
    {
        $capacity = 100;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 10:00:00', $result['dateTime']);
        $this->assertEquals(0, $result['occupiedByVehicles']);
        $this->assertEquals(0, $result['reservedSpaces']);
        $this->assertEquals(0, $result['totalOccupied']);
        $this->assertEquals(100, $result['availableSpaces']);
    }

    public function testExecuteWithOccupiedSpaces(): void
    {
        $capacity = 50;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        // Create mock occupied parking spaces
        $mockSpace1 = $this->createMock(ParkingSpace::class);
        $mockSpace1->method('getStartTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
        $mockSpace1->method('getEndTime')->willReturn(null); // Still occupied

        $mockSpace2 = $this->createMock(ParkingSpace::class);
        $mockSpace2->method('getStartTime')->willReturn(new DateTime('2024-01-01 08:00:00'));
        $mockSpace2->method('getEndTime')->willReturn(new DateTime('2024-01-01 11:00:00'));

        $parkingSpaces = [$mockSpace1, $mockSpace2];

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 10:00:00', $result['dateTime']);
        $this->assertEquals(2, $result['occupiedByVehicles']);
        $this->assertEquals(0, $result['reservedSpaces']);
        $this->assertEquals(2, $result['totalOccupied']);
        $this->assertEquals(48, $result['availableSpaces']);
    }

    public function testExecuteWithReservedSpaces(): void
    {
        $capacity = 30;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        // Create mock reservations
        $mockReservation1 = $this->createMock(Reservation::class);
        $mockReservation1->method('getStartTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
        $mockReservation1->method('getEndTime')->willReturn(new DateTime('2024-01-01 11:00:00'));

        $mockReservation2 = $this->createMock(Reservation::class);
        $mockReservation2->method('getStartTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation2->method('getEndTime')->willReturn(new DateTime('2024-01-01 14:00:00'));

        $reservations = [$mockReservation1, $mockReservation2];

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);
        $this->mockParking->method('getReservations')->willReturn($reservations);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 10:00:00', $result['dateTime']);
        $this->assertEquals(0, $result['occupiedByVehicles']);
        $this->assertEquals(1, $result['reservedSpaces']); // Only first reservation is active at 10:00
        $this->assertEquals(1, $result['totalOccupied']);
        $this->assertEquals(29, $result['availableSpaces']);
    }

    public function testExecuteWithMixedOccupiedAndReservedSpaces(): void
    {
        $capacity = 20;
        $dateTime = new DateTime('2024-01-01 15:00:00');

        // Create mock occupied parking spaces
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 14:00:00'));
        $mockSpace->method('getEndTime')->willReturn(null);

        $parkingSpaces = [$mockSpace];

        // Create mock reservations
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('2024-01-01 14:30:00'));
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-01-01 16:00:00'));

        $reservations = [$mockReservation];

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getReservations')->willReturn($reservations);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 15:00:00', $result['dateTime']);
        $this->assertEquals(1, $result['occupiedByVehicles']);
        $this->assertEquals(1, $result['reservedSpaces']);
        $this->assertEquals(2, $result['totalOccupied']);
        $this->assertEquals(18, $result['availableSpaces']);
    }

    public function testExecuteWithZeroAvailableSpaces(): void
    {
        $capacity = 2;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        // Create mock occupied parking spaces
        $mockSpace1 = $this->createMock(ParkingSpace::class);
        $mockSpace1->method('getStartTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
        $mockSpace1->method('getEndTime')->willReturn(null);

        $mockSpace2 = $this->createMock(ParkingSpace::class);
        $mockSpace2->method('getStartTime')->willReturn(new DateTime('2024-01-01 08:00:00'));
        $mockSpace2->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));

        $parkingSpaces = [$mockSpace1, $mockSpace2];

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 10:00:00', $result['dateTime']);
        $this->assertEquals(2, $result['occupiedByVehicles']);
        $this->assertEquals(0, $result['reservedSpaces']);
        $this->assertEquals(2, $result['totalOccupied']);
        $this->assertEquals(0, $result['availableSpaces']);
    }

    public function testExecuteWithMoreOccupiedThanCapacity(): void
    {
        $capacity = 10;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        // Simulate a scenario where there are more occupied spaces than capacity
        $parkingSpaces = [];
        for ($i = 0; $i < 12; $i++) {
            $mockSpace = $this->createMock(ParkingSpace::class);
            $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
            $mockSpace->method('getEndTime')->willReturn(null);
            $parkingSpaces[] = $mockSpace;
        }

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-01-01 10:00:00', $result['dateTime']);
        $this->assertEquals(12, $result['occupiedByVehicles']);
        $this->assertEquals(0, $result['reservedSpaces']);
        $this->assertEquals(12, $result['totalOccupied']);
        $this->assertEquals(0, $result['availableSpaces']); // max(0, -2) = 0
    }

    public function testExecuteWithDifferentDateTimeFormats(): void
    {
        $capacity = 50;
        $dateTime = new DateTime('2024-12-31 23:59:59');

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertEquals('2024-12-31 23:59:59', $result['dateTime']);
        $this->assertEquals(0, $result['occupiedByVehicles']);
        $this->assertEquals(0, $result['reservedSpaces']);
        $this->assertEquals(0, $result['totalOccupied']);
        $this->assertEquals(50, $result['availableSpaces']);
    }

    public function testExecuteEnsuresAvailableSpacesNeverNegative(): void
    {
        $capacity = 5;
        $dateTime = new DateTime('2024-01-01 10:00:00');

        // Create more occupied spaces than capacity
        $parkingSpaces = [];
        for ($i = 0; $i < 10; $i++) {
            $mockSpace = $this->createMock(ParkingSpace::class);
            $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
            $mockSpace->method('getEndTime')->willReturn(null);
            $parkingSpaces[] = $mockSpace;
        }

        $this->mockParking->method('getCapacity')->willReturn($capacity);
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingAvailabilityUseCase->execute($this->mockParking, $dateTime);

        $this->assertGreaterThanOrEqual(0, $result['availableSpaces']);
        $this->assertEquals(0, $result['availableSpaces']);
    }
}
