<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../../vendor/autoload.php';

class MySQLParkingRepositoryTest extends TestCase
{
    private MockObject $pdo;
    private MySQLParkingRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        
        // Inject mock PDO into MySQLFactory
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $this->repository = new MySQLParkingRepository();
    }

    protected function tearDown(): void
    {
        // Reset MySQLFactory
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testFindByIdFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with([1]);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'total_spaces' => 100,
            'hourly_rate' => 5.0,
            'owner_id' => 1
        ]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM parkings WHERE id = ? LIMIT 1")
            ->willReturn($stmt);

        $parking = $this->repository->findById(1);

        $this->assertNotNull($parking);
        $this->assertEquals(1, $parking->getId());
        $this->assertEquals(100, $parking->getCapacity());
    }

    public function testFindByIdNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with([999]);
        $stmt->method('fetch')->willReturn(false);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $parking = $this->repository->findById(999);

        $this->assertNull($parking);
    }

    public function testSaveInsert(): void
    {
        $parking = new Parking(['latitude' => 1.0, 'longitude' => 2.0], 50);
        // Note: Parking constructor might not set ID, so it's null (insert)

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO parkings'))
            ->willReturn($stmt);
            
        $this->pdo->method('lastInsertId')->willReturn('123');

        $this->repository->save($parking);

        $this->assertEquals(123, $parking->getId());
    }

    public function testSaveUpdate(): void
    {
        $parking = new Parking(['latitude' => 1.0, 'longitude' => 2.0], 50);
        $parking->setId(1);

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE parkings'))
            ->willReturn($stmt);

        $this->repository->save($parking);
    }

    public function testFindAll(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([
            [
                'id' => 1,
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'total_spaces' => 100,
                'hourly_rate' => 5.0,
                'owner_id' => 1
            ]
        ]);

        $this->pdo->expects($this->once())
            ->method('query')
            ->with("SELECT * FROM parkings")
            ->willReturn($stmt);

        $parkings = $this->repository->findAll();

        $this->assertCount(1, $parkings);
        $this->assertEquals(1, $parkings[0]->getId());
    }
}
