<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class ExitParkingUseCaseTest extends TestCase
{
    private ExitParkingUseCase $exitParkingUseCase;

    protected function setUp(): void
    {
        $this->exitParkingUseCase = new ExitParkingUseCase();
    }

    public function testExecuteOnTime(): void
    {
        $exitTime = new DateTime('2024-01-01 12:00:00');
        $reservationEndTime = new DateTime('2024-01-01 14:00:00');

        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockReservation = $this->createMock(Reservation::class);
        
        $mockReservation->method('getEndTime')->willReturn($reservationEndTime);
        $mockParkingSpace->method('getReservation')->willReturn($mockReservation);
        
        $mockParkingSpace->expects($this->once())->method('setEndTime')->with($exitTime);
        $mockParkingSpace->expects($this->once())->method('setPenaltyAmount')->with(0.0);

        $this->exitParkingUseCase->execute($mockParkingSpace, $exitTime);
    }

    public function testExecuteOvertime(): void
    {
        $reservationEndTime = new DateTime('2024-01-01 12:00:00');
        $exitTime = new DateTime('2024-01-01 14:00:00'); // 2 hours overtime

        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockReservation = $this->createMock(Reservation::class);
        $mockParking = $this->createMock(Parking::class);
        $mockPricingSchedule = $this->createMock(PricingSchedule::class);
        
        $mockPricingSchedule->method('getTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockPricingSchedule->method('getPrice')->willReturn(2.0);
        $mockParking->method('getPricingSchedules')->willReturn([$mockPricingSchedule]);
        $mockReservation->method('getEndTime')->willReturn($reservationEndTime);
        $mockParkingSpace->method('getReservation')->willReturn($mockReservation);
        $mockParkingSpace->method('getParking')->willReturn($mockParking);

        $mockParkingSpace->expects($this->once())->method('setEndTime')->with($exitTime);
        $mockParkingSpace->expects($this->once())->method('setPenaltyAmount')->with(24.0); // 20 base + 4 overtime

        $this->exitParkingUseCase->execute($mockParkingSpace, $exitTime);
    }

    public function testExecuteExactTime(): void
    {
        $time = new DateTime('2024-01-01 12:00:00');

        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockReservation = $this->createMock(Reservation::class);
        
        $mockReservation->method('getEndTime')->willReturn($time);
        $mockParkingSpace->method('getReservation')->willReturn($mockReservation);
        
        $mockParkingSpace->expects($this->once())->method('setEndTime');
        $mockParkingSpace->expects($this->once())->method('setPenaltyAmount')->with(0.0);

        $this->exitParkingUseCase->execute($mockParkingSpace, $time);
    }
}