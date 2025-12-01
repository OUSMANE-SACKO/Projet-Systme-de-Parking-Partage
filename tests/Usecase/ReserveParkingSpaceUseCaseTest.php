<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class ReserveParkingSpaceUseCaseTest extends TestCase
{
    public function testExecuteSuccessfulReservations(): void
    {
        $successCases = [
            'basic_valid' => [new DateTime('2024-01-01 10:00:00'), new DateTime('2024-01-01 12:00:00')],
            'one_day' => [new DateTime('2024-01-01 09:00:00'), new DateTime('2024-01-02 18:00:00')],
            'very_short' => [new DateTime('2024-01-01 10:00:00'), new DateTime('2024-01-01 10:00:01')],
            'future_reservation' => [new DateTime('+1 month'), new DateTime('+1 month +2 hours')]
        ];

        foreach ($successCases as $caseName => $times) {
            [$startTime, $endTime] = $times;
            
            $useCase = new ReserveParkingSpaceUseCase();
            $mockCustomer = $this->createMock(Customer::class);
            $mockParking = $this->createMock(Parking::class);

            $mockParking->expects($this->once())
                ->method('hasAvailableSpace')
                ->willReturn(true);

            $mockParking->expects($this->once())
                ->method('addReservation')
                ->with($this->isInstanceOf(Reservation::class));

            $mockCustomer->expects($this->once())
                ->method('addReservation')
                ->with($this->isInstanceOf(Reservation::class));

            $result = $useCase->execute($mockCustomer, $mockParking, $startTime, $endTime);
            $this->assertInstanceOf(Reservation::class, $result, "Failed for case: {$caseName}");
        }
    }

    public function testExecuteValidationErrors(): void
    {
        $errorCases = [
            'end_before_start' => [
                'start' => new DateTime('2024-01-01 12:00:00'),
                'end' => new DateTime('2024-01-01 10:00:00'),
                'expected_message' => 'End time must be after start time.',
                'exception_class' => InvalidArgumentException::class,
                'check_availability' => false
            ],
            'end_equal_start' => [
                'start' => new DateTime('2024-01-01 10:00:00'),
                'end' => new DateTime('2024-01-01 10:00:00'),
                'expected_message' => 'End time must be after start time.',
                'exception_class' => InvalidArgumentException::class,
                'check_availability' => false
            ],
            'parking_full' => [
                'start' => new DateTime('2024-01-01 10:00:00'),
                'end' => new DateTime('2024-01-01 12:00:00'),
                'expected_message' => 'Parking is full.',
                'exception_class' => RuntimeException::class,
                'check_availability' => true,
                'availability_result' => false
            ]
        ];

        foreach ($errorCases as $caseName => $case) {
            $useCase = new ReserveParkingSpaceUseCase();
            $mockCustomer = $this->createMock(Customer::class);
            $mockParking = $this->createMock(Parking::class);

            if ($case['check_availability']) {
                $mockParking->expects($this->once())
                    ->method('hasAvailableSpace')
                    ->willReturn($case['availability_result']);
            } else {
                $mockParking->expects($this->never())
                    ->method('hasAvailableSpace');
            }

            $this->expectException($case['exception_class']);
            $this->expectExceptionMessage($case['expected_message']);

            $useCase->execute($mockCustomer, $mockParking, $case['start'], $case['end']);
        }
    }
}