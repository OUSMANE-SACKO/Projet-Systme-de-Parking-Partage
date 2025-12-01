<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetParkingSpacesUseCaseTest extends TestCase
{
    private GetParkingSpacesUseCase $getParkingSpacesUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->getParkingSpacesUseCase = new GetParkingSpacesUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteWithNoParkingSpaces(): void
    {
        $capacity = 100;
        $this->mockParking->method('getParkingSpaces')->willReturn([]);
        $this->mockParking->method('getCapacity')->willReturn($capacity);

        $result = $this->getParkingSpacesUseCase->execute($this->mockParking);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['occupied']);
        $this->assertEquals($capacity, $result['available']);
        $this->assertEquals($capacity, $result['capacity']);
        $this->assertIsArray($result['parkingSpaces']);
        $this->assertEmpty($result['parkingSpaces']);
    }

    public function testExecuteWithOccupiedSpaces(): void
    {
        $capacity = 50;
        
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->method('getId')->willReturn('cust1');
        $mockCustomer->method('getEmail')->willReturn('test@example.com');

        $mockSpace1 = $this->createMock(ParkingSpace::class);
        $mockSpace1->method('getId')->willReturn('space1');
        $mockSpace1->method('getCustomer')->willReturn($mockCustomer);
        $mockSpace1->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockSpace1->method('getEndTime')->willReturn(null); // Still occupied

        $mockSpace2 = $this->createMock(ParkingSpace::class);
        $mockSpace2->method('getId')->willReturn('space2');
        $mockSpace2->method('getCustomer')->willReturn($mockCustomer);
        $mockSpace2->method('getStartTime')->willReturn(new DateTime('2024-01-01 08:00:00'));
        $mockSpace2->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00')); // Completed

        $parkingSpaces = [$mockSpace1, $mockSpace2];
        
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getCapacity')->willReturn($capacity);

        $result = $this->getParkingSpacesUseCase->execute($this->mockParking);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['occupied']); // Only space1 is occupied
        $this->assertEquals(49, $result['available']);
        $this->assertEquals($capacity, $result['capacity']);
        $this->assertCount(2, $result['parkingSpaces']);
    }

    public function testExecuteWithAllOccupiedSpaces(): void
    {
        $capacity = 10;
        
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->method('getId')->willReturn('cust1');
        $mockCustomer->method('getEmail')->willReturn('test@example.com');

        $parkingSpaces = [];
        for ($i = 0; $i < 10; $i++) {
            $mockSpace = $this->createMock(ParkingSpace::class);
            $mockSpace->method('getId')->willReturn('space' . $i);
            $mockSpace->method('getCustomer')->willReturn($mockCustomer);
            $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
            $mockSpace->method('getEndTime')->willReturn(null); // All occupied
            $parkingSpaces[] = $mockSpace;
        }
        
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getCapacity')->willReturn($capacity);

        $result = $this->getParkingSpacesUseCase->execute($this->mockParking);

        $this->assertEquals(10, $result['total']);
        $this->assertEquals(10, $result['occupied']);
        $this->assertEquals(0, $result['available']);
        $this->assertEquals($capacity, $result['capacity']);
    }

    public function testGetOccupiedSpaces(): void
    {
        $capacity = 20;
        
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->method('getId')->willReturn('cust1');
        $mockCustomer->method('getEmail')->willReturn('test@example.com');

        $mockOccupiedSpace = $this->createMock(ParkingSpace::class);
        $mockOccupiedSpace->method('getId')->willReturn('occupied1');
        $mockOccupiedSpace->method('getCustomer')->willReturn($mockCustomer);
        $mockOccupiedSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockOccupiedSpace->method('getEndTime')->willReturn(null); // Still occupied

        $mockCompletedSpace = $this->createMock(ParkingSpace::class);
        $mockCompletedSpace->method('getId')->willReturn('completed1');
        $mockCompletedSpace->method('getCustomer')->willReturn($mockCustomer);
        $mockCompletedSpace->method('getStartTime')->willReturn(new DateTime('2024-01-01 08:00:00'));
        $mockCompletedSpace->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00')); // Completed

        $parkingSpaces = [$mockOccupiedSpace, $mockCompletedSpace];
        
        $this->mockParking->method('getParkingSpaces')->willReturn($parkingSpaces);
        $this->mockParking->method('getCapacity')->willReturn($capacity);

        $occupiedSpaces = $this->getParkingSpacesUseCase->getOccupiedSpaces($this->mockParking);

        $this->assertIsArray($occupiedSpaces);
        $this->assertCount(1, $occupiedSpaces);
        $this->assertEquals('occupied', $occupiedSpaces[0]['status']);
    }
}