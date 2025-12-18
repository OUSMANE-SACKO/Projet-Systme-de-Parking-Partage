<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetParkingMonthlyRevenueUseCaseTest extends TestCase
{
    private GetParkingMonthlyRevenueUseCase $getParkingMonthlyRevenueUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->getParkingMonthlyRevenueUseCase = new GetParkingMonthlyRevenueUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteWithNoRevenueData(): void
    {
        $month = 1;
        $year = 2024;
        
        $this->mockParking->method('getReservations')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals('2024-01', $result['month']);
        $this->assertEquals(0.0, $result['reservationsRevenue']);
        $this->assertEquals(0.0, $result['subscriptionsRevenue']);
        $this->assertEquals(0.0, $result['totalRevenue']);
    }

    public function testExecuteWithOnlyReservationsRevenue(): void
    {
        $month = 3;
        $year = 2024;
        
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-03-15 12:00:00'));
        $mockReservation->method('getAmount')->willReturn(50.0);
        
        $mockInvoice = $this->createMock(Invoice::class);
        $mockInvoice->method('getAmount')->willReturn(50.0);
        
        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals('2024-03', $result['month']);
        $this->assertEquals(50.0, $result['reservationsRevenue']);
        $this->assertEquals(0.0, $result['subscriptionsRevenue']);
        $this->assertEquals(50.0, $result['totalRevenue']);
    }

    public function testExecuteWithReservationsOutsideMonth(): void
    {
        $month = 3;
        $year = 2024;
        
        $reservationBeforeMonth = $this->createMock(Reservation::class);
        $reservationBeforeMonth->method('getEndTime')->willReturn(new DateTime('2024-02-28 12:00:00'));
        
        $reservationAfterMonth = $this->createMock(Reservation::class);
        $reservationAfterMonth->method('getEndTime')->willReturn(new DateTime('2024-04-01 12:00:00'));
        
        $this->mockParking->method('getReservations')->willReturn([$reservationBeforeMonth, $reservationAfterMonth]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals(0.0, $result['reservationsRevenue']);
        $this->assertEquals(0.0, $result['totalRevenue']);
    }

    public function testExecuteWithSubscriptionRevenue(): void
    {
        $month = 6;
        $year = 2024;
        
        $mockCustomer = $this->createMock(Customer::class);
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getMonthlyPrice')->willReturn(75.0);
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-06-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-06-30'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        
        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockParkingSpace->method('getCustomer')->willReturn($mockCustomer);
        
        $this->mockParking->method('getReservations')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockParkingSpace]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals('2024-06', $result['month']);
        $this->assertEquals(0.0, $result['reservationsRevenue']);
        $this->assertEquals(75.0, $result['subscriptionsRevenue']);
        $this->assertEquals(75.0, $result['totalRevenue']);
    }

    public function testExecuteWithCombinedRevenue(): void
    {
        $month = 7;
        $year = 2024;
        
        // Mock reservation revenue
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-07-20 12:00:00'));
        $mockReservation->method('getAmount')->willReturn(30.0);
        
        $mockInvoice = $this->createMock(Invoice::class);
        $mockInvoice->method('getAmount')->willReturn(30.0);
        
        // Mock subscription revenue
        $mockCustomer = $this->createMock(Customer::class);
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getMonthlyPrice')->willReturn(100.0);
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-07-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-07-31'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        
        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockParkingSpace->method('getCustomer')->willReturn($mockCustomer);
        
        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockParkingSpace]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals('2024-07', $result['month']);
        $this->assertEquals(30.0, $result['reservationsRevenue']);
        $this->assertEquals(100.0, $result['subscriptionsRevenue']);
        $this->assertEquals(130.0, $result['totalRevenue']);
    }

    public function testExecuteWithDecimalValues(): void
    {
        $month = 8;
        $year = 2024;
        
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-08-15 12:00:00'));
        $mockReservation->method('getAmount')->willReturn(25.567); // Should be rounded
        
        $mockInvoice = $this->createMock(Invoice::class);
        $mockInvoice->method('getAmount')->willReturn(25.567); // Should be rounded
        
        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals(25.57, $result['reservationsRevenue']); // Rounded to 2 decimal places
        $this->assertEquals(25.57, $result['totalRevenue']);
    }

    public function testExecuteWithMultipleReservations(): void
    {
        $month = 9;
        $year = 2024;
        
        $mockReservation1 = $this->createMock(Reservation::class);
        $mockReservation1->method('getEndTime')->willReturn(new DateTime('2024-09-10 12:00:00'));
        $mockReservation1->method('getAmount')->willReturn(15.0);
        
        $mockReservation2 = $this->createMock(Reservation::class);
        $mockReservation2->method('getEndTime')->willReturn(new DateTime('2024-09-20 14:00:00'));
        $mockReservation2->method('getAmount')->willReturn(25.0);
        
        $mockInvoice1 = $this->createMock(Invoice::class);
        $mockInvoice1->method('getAmount')->willReturn(15.0);
        
        $mockInvoice2 = $this->createMock(Invoice::class);
        $mockInvoice2->method('getAmount')->willReturn(25.0);
        
        $this->mockParking->method('getReservations')->willReturn([$mockReservation1, $mockReservation2]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals(40.0, $result['reservationsRevenue']); // 15 + 25
        $this->assertEquals(40.0, $result['totalRevenue']);
    }

    public function testExecuteFormatsMonthCorrectly(): void
    {
        $month = 2;
        $year = 2024;
        
        $this->mockParking->method('getReservations')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        $this->assertEquals('2024-02', $result['month']); // Should pad month with zero
    }

    public function testExecuteWithSubscriptionSpanningMultipleMonths(): void
    {
        $month = 5;
        $year = 2024;
        
        $mockCustomer = $this->createMock(Customer::class);
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getMonthlyPrice')->willReturn(60.0);
        
        // Subscription spans from April to June
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-04-15'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-06-15'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        
        $mockParkingSpace = $this->createMock(ParkingSpace::class);
        $mockParkingSpace->method('getCustomer')->willReturn($mockCustomer);
        
        $this->mockParking->method('getReservations')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockParkingSpace]);

        $result = $this->getParkingMonthlyRevenueUseCase->execute($this->mockParking, $month, $year);

        // Should include subscription revenue for May since it overlaps
        $this->assertEquals(60.0, $result['subscriptionsRevenue']);
        $this->assertEquals(60.0, $result['totalRevenue']);
    }
}
