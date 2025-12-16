<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetParkingReservationsUseCaseTest extends TestCase
{
    private GetParkingReservationsUseCase $getParkingReservationsUseCase;
    private MockObject $mockParking;
    private MockObject $mockCustomer;

    protected function setUp(): void
    {
        $this->getParkingReservationsUseCase = new GetParkingReservationsUseCase();
        $this->mockParking = $this->createMock(Parking::class);
        $this->mockCustomer = $this->createMock(Customer::class);
        
        $this->mockCustomer->method('getId')->willReturn(1);
        $this->mockCustomer->method('getEmail')->willReturn('customer@example.com');
    }

    public function testExecuteWithSingleReservation(): void
    {
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getId')->willReturn(1);
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation->method('getDurationMinutes')->willReturn(120);
        
        // Status logic in use case might depend on current time, so we just check it exists
        // Or we can mock it if the use case calls a method on reservation to get status?
        // Usually use case calculates status based on time.

        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals(1, $result['total']);
        $this->assertCount(1, $result['reservations']);
        
        $reservation = $result['reservations'][0];
        $this->assertEquals(1, $reservation['id']);
        $this->assertEquals(1, $reservation['customer']['id']);
        $this->assertEquals('customer@example.com', $reservation['customer']['email']);
        $this->assertEquals('2024-01-01 10:00:00', $reservation['startTime']);
        $this->assertEquals('2024-01-01 12:00:00', $reservation['endTime']);
        $this->assertEquals(120, $reservation['duration']);
        $this->assertArrayHasKey('status', $reservation);
    }

    public function testExecuteWithMultipleReservations(): void
    {
        $mockReservation1 = $this->createMock(Reservation::class);
        $mockReservation1->method('getId')->willReturn(1);
        $mockReservation1->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation1->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockReservation1->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation1->method('getDurationMinutes')->willReturn(120);

        $mockReservation2 = $this->createMock(Reservation::class);
        $mockReservation2->method('getId')->willReturn(2);
        $mockReservation2->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation2->method('getStartTime')->willReturn(new DateTime('2024-01-02 14:00:00'));
        $mockReservation2->method('getEndTime')->willReturn(new DateTime('2024-01-02 16:00:00'));
        $mockReservation2->method('getDurationMinutes')->willReturn(120);

        $reservations = [$mockReservation1, $mockReservation2];
        $this->mockParking->method('getReservations')->willReturn($reservations);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals(2, $result['total']);
        $this->assertCount(2, $result['reservations']);
        
        // Check if sorted by start time (descending)
        // The use case likely sorts them. If so, res2 (Jan 2) should be before res1 (Jan 1)
        $this->assertEquals(2, $result['reservations'][0]['id']); 
        $this->assertEquals(1, $result['reservations'][1]['id']);
    }
}
