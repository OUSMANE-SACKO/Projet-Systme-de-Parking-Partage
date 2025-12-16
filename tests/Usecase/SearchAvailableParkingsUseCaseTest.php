<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class SearchAvailableParkingsUseCaseTest extends TestCase
{
    private SearchAvailableParkingsUseCase $searchAvailableParkingsUseCase;
    private MockObject $mockParkingRepository;

    protected function setUp(): void
    {
        $this->mockParkingRepository = $this->createMock(IParkingRepository::class);
        $this->searchAvailableParkingsUseCase = new SearchAvailableParkingsUseCase($this->mockParkingRepository);
    }

    public function testExecuteWithNoParkingsFound(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $radiusKm = 5.0;

        $this->mockParkingRepository->expects($this->once())
            ->method('findByLocation')
            ->with($latitude, $longitude, $radiusKm)
            ->willReturn([]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, $radiusKm);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['parkings']);
        $this->assertEquals($latitude, $result['searchCenter']['latitude']);
        $this->assertEquals($longitude, $result['searchCenter']['longitude']);
        $this->assertEquals($radiusKm, $result['radiusKm']);
    }

    public function testExecuteWithAvailableParkings(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $radiusKm = 5.0;

        $mockParking = $this->createMock(Parking::class);
        $mockParking->method('getLocation')->willReturn([
            'latitude' => 48.8570,
            'longitude' => 2.3530,
            'address' => '123 Test Street'
        ]);
        $mockParking->method('getCapacity')->willReturn(50);
        $mockParking->method('getParkingSpaces')->willReturn([]);
        $mockParking->method('getReservations')->willReturn([]);

        $this->mockParkingRepository->expects($this->once())
            ->method('findByLocation')
            ->with($latitude, $longitude, $radiusKm)
            ->willReturn([$mockParking]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, $radiusKm);

        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['parkings']);
        
        $parking = $result['parkings'][0];
        $this->assertSame($mockParking, $parking['parking']);
        $this->assertIsFloat($parking['distance']);
        $this->assertGreaterThanOrEqual(0, $parking['distance']);
        $this->assertEquals(50, $parking['availableSpaces']);
        $this->assertEquals(50, $parking['capacity']);
    }

    public function testExecuteWithParkingsMissingCoordinates(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;

        $mockParkingWithoutCoords = $this->createMock(Parking::class);
        $mockParkingWithoutCoords->method('getLocation')->willReturn([
            'address' => '123 Test Street'
            // Missing latitude and longitude
        ]);

        $mockParkingWithCoords = $this->createMock(Parking::class);
        $mockParkingWithCoords->method('getLocation')->willReturn([
            'latitude' => 48.8570,
            'longitude' => 2.3530,
            'address' => '456 Valid Street'
        ]);
        $mockParkingWithCoords->method('getCapacity')->willReturn(30);
        $mockParkingWithCoords->method('getParkingSpaces')->willReturn([]);
        $mockParkingWithCoords->method('getReservations')->willReturn([]);

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([$mockParkingWithoutCoords, $mockParkingWithCoords]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude);

        // Should only include parking with valid coordinates
        $this->assertEquals(1, $result['count']);
        $this->assertSame($mockParkingWithCoords, $result['parkings'][0]['parking']);
    }

    public function testExecuteWithParkingsOutsideRadius(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $radiusKm = 1.0; // Very small radius

        $mockNearParking = $this->createMock(Parking::class);
        $mockNearParking->method('getLocation')->willReturn([
            'latitude' => 48.8567, // Very close
            'longitude' => 2.3523,
            'address' => 'Near Parking'
        ]);
        $mockNearParking->method('getCapacity')->willReturn(20);
        $mockNearParking->method('getParkingSpaces')->willReturn([]);
        $mockNearParking->method('getReservations')->willReturn([]);

        $mockFarParking = $this->createMock(Parking::class);
        $mockFarParking->method('getLocation')->willReturn([
            'latitude' => 49.0000, // Far away
            'longitude' => 3.0000,
            'address' => 'Far Parking'
        ]);

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([$mockNearParking, $mockFarParking]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, $radiusKm);

        // Should only include near parking
        $this->assertEquals(1, $result['count']);
        $this->assertSame($mockNearParking, $result['parkings'][0]['parking']);
    }

    public function testExecuteWithFullParkings(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $checkTime = new DateTime('2024-01-01 12:00:00'); // Explicit check time

        $mockFullParking = $this->createMock(Parking::class);
        $mockFullParking->method('getLocation')->willReturn([
            'latitude' => 48.8570,
            'longitude' => 2.3530
        ]);
        $mockFullParking->method('getCapacity')->willReturn(2);
        
        // Create occupied spaces
        $mockSpace1 = $this->createMock(ParkingSpace::class);
        $mockSpace1->method('getStartTime')->willReturn(new DateTime('2024-01-01 10:00:00'));
        $mockSpace1->method('getEndTime')->willReturn(null);
        $mockSpace2 = $this->createMock(ParkingSpace::class);
        $mockSpace2->method('getStartTime')->willReturn(new DateTime('2024-01-01 11:00:00'));
        $mockSpace2->method('getEndTime')->willReturn(null);
        
        $mockFullParking->method('getParkingSpaces')->willReturn([$mockSpace1, $mockSpace2]);
        $mockFullParking->method('getReservations')->willReturn([]);

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([$mockFullParking]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, 5.0, $checkTime);

        // Should not include full parking
        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['parkings']);
    }

    public function testExecuteWithDefaultCheckTime(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude);

        $this->assertArrayHasKey('checkTime', $result);
        $this->assertNotEmpty($result['checkTime']);
        
        // Check that it's a valid datetime format
        $checkTime = DateTime::createFromFormat('Y-m-d H:i:s', $result['checkTime']);
        $this->assertInstanceOf(DateTime::class, $checkTime);
    }

    public function testExecuteWithCustomCheckTime(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $customTime = new DateTime('2024-12-25 15:30:00');

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, 5.0, $customTime);

        $this->assertEquals('2024-12-25 15:30:00', $result['checkTime']);
    }

    public function testExecuteSortsByDistance(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;

        $mockNearParking = $this->createMock(Parking::class);
        $mockNearParking->method('getLocation')->willReturn([
            'latitude' => 48.8567, // Closer
            'longitude' => 2.3523
        ]);
        $mockNearParking->method('getCapacity')->willReturn(20);
        $mockNearParking->method('getParkingSpaces')->willReturn([]);
        $mockNearParking->method('getReservations')->willReturn([]);

        $mockFarParking = $this->createMock(Parking::class);
        $mockFarParking->method('getLocation')->willReturn([
            'latitude' => 48.8580, // Farther
            'longitude' => 2.3550
        ]);
        $mockFarParking->method('getCapacity')->willReturn(30);
        $mockFarParking->method('getParkingSpaces')->willReturn([]);
        $mockFarParking->method('getReservations')->willReturn([]);

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([$mockFarParking, $mockNearParking]); // Return far first

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude);

        $this->assertEquals(2, $result['count']);
        
        // Should be sorted by distance (near first)
        $this->assertSame($mockNearParking, $result['parkings'][0]['parking']);
        $this->assertSame($mockFarParking, $result['parkings'][1]['parking']);
        $this->assertLessThan($result['parkings'][1]['distance'], $result['parkings'][0]['distance']);
    }

    public function testExecuteWithDefaultRadius(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;

        $this->mockParkingRepository->expects($this->once())
            ->method('findByLocation')
            ->with($latitude, $longitude, 5.0) // Default radius
            ->willReturn([]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude);

        $this->assertEquals(5.0, $result['radiusKm']);
    }

    public function testExecuteReturnsCorrectStructure(): void
    {
        $latitude = 48.8566;
        $longitude = 2.3522;
        $radiusKm = 3.0;

        $this->mockParkingRepository->method('findByLocation')
            ->willReturn([]);

        $result = $this->searchAvailableParkingsUseCase->execute($latitude, $longitude, $radiusKm);

        $this->assertArrayHasKey('parkings', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('searchCenter', $result);
        $this->assertArrayHasKey('radiusKm', $result);
        $this->assertArrayHasKey('checkTime', $result);
        
        $this->assertArrayHasKey('latitude', $result['searchCenter']);
        $this->assertArrayHasKey('longitude', $result['searchCenter']);
        $this->assertEquals($latitude, $result['searchCenter']['latitude']);
        $this->assertEquals($longitude, $result['searchCenter']['longitude']);
    }
}
