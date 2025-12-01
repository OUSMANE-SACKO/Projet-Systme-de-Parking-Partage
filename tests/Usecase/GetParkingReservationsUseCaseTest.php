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
        
        $this->mockCustomer->method('getId')->willReturn('customer123');
        $this->mockCustomer->method('getEmail')->willReturn('customer@example.com');
    }

    public function testExecuteWithNoReservations(): void
    {
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals(0, $result['total']);
        $this->assertIsArray($result['reservations']);
        $this->assertEmpty($result['reservations']);
    }

    public function testExecuteWithSingleReservation(): void
    {
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getId')->willReturn('res123');
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation->method('getDurationMinutes')->willReturn(120); // 2 hours in minutes

        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals(1, $result['total']);
        $this->assertCount(1, $result['reservations']);
        
        $reservation = $result['reservations'][0];
        $this->assertEquals('res123', $reservation['id']);
        $this->assertEquals('customer123', $reservation['customer']['id']);
        $this->assertEquals('customer@example.com', $reservation['customer']['email']);
        $this->assertEquals('2024-01-01 10:00:00', $reservation['startTime']);
        $this->assertEquals('2024-01-01 12:00:00', $reservation['endTime']);
        $this->assertEquals(120, $reservation['duration']);
        $this->assertContains($reservation['status'], ['active', 'upcoming', 'past']);
    }

    public function testExecuteWithMultipleReservations(): void
    {
        $mockReservation1 = $this->createMock(Reservation::class);
        $mockReservation1->method('getId')->willReturn('res1');
        $mockReservation1->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation1->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockReservation1->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation1->method('getDurationMinutes')->willReturn(120);

        $mockReservation2 = $this->createMock(Reservation::class);
        $mockReservation2->method('getId')->willReturn('res2');
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
        $this->assertEquals('res2', $result['reservations'][0]['id']); // More recent first
        $this->assertEquals('res1', $result['reservations'][1]['id']);
    }

    public function testExecuteReservationStatusActive(): void
    {
        $now = new DateTime('2024-01-01 11:00:00');
        
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getId')->willReturn('res123');
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('-1 hour')); // Started 1 hour ago
        $mockReservation->method('getEndTime')->willReturn(new DateTime('+1 hour')); // Ends in 1 hour
        $mockReservation->method('getDurationMinutes')->willReturn(120);

        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        // Mock the current time for testing
        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals('active', $result['reservations'][0]['status']);
    }

    public function testExecuteReservationStatusUpcoming(): void
    {
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getId')->willReturn('res123');
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('+1 hour')); // Future
        $mockReservation->method('getEndTime')->willReturn(new DateTime('+3 hours'));
        $mockReservation->method('getDurationMinutes')->willReturn(120);

        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals('upcoming', $result['reservations'][0]['status']);
    }

    public function testExecuteReservationStatusPast(): void
    {
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getId')->willReturn('res123');
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('-3 hours')); // Past
        $mockReservation->method('getEndTime')->willReturn(new DateTime('-1 hour')); // Past
        $mockReservation->method('getDurationMinutes')->willReturn(120);

        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        $this->assertEquals('past', $result['reservations'][0]['status']);
    }

    public function testExecuteSortsByStartTimeDescending(): void
    {
        $mockReservation1 = $this->createMock(Reservation::class);
        $mockReservation1->method('getId')->willReturn('res1');
        $mockReservation1->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation1->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockReservation1->method('getEndTime')->willReturn(new DateTime('2024-01-01 12:00:00'));
        $mockReservation1->method('getDurationMinutes')->willReturn(120);

        $mockReservation2 = $this->createMock(Reservation::class);
        $mockReservation2->method('getId')->willReturn('res2');
        $mockReservation2->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation2->method('getStartTime')->willReturn(new DateTime('2024-01-03 10:00:00')); // Later date
        $mockReservation2->method('getEndTime')->willReturn(new DateTime('2024-01-03 12:00:00'));
        $mockReservation2->method('getDurationMinutes')->willReturn(120);

        $mockReservation3 = $this->createMock(Reservation::class);
        $mockReservation3->method('getId')->willReturn('res3');
        $mockReservation3->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation3->method('getStartTime')->willReturn(new DateTime('2024-01-02 10:00:00')); // Middle date
        $mockReservation3->method('getEndTime')->willReturn(new DateTime('2024-01-02 12:00:00'));
        $mockReservation3->method('getDurationMinutes')->willReturn(120);

        $reservations = [$mockReservation1, $mockReservation2, $mockReservation3];
        $this->mockParking->method('getReservations')->willReturn($reservations);

        $result = $this->getParkingReservationsUseCase->execute($this->mockParking);

        // Should be sorted by start time descending (most recent first)
        $this->assertEquals('res2', $result['reservations'][0]['id']); // 2024-01-03
        $this->assertEquals('res3', $result['reservations'][1]['id']); // 2024-01-02
        $this->assertEquals('res1', $result['reservations'][2]['id']); // 2024-01-01
    }
}