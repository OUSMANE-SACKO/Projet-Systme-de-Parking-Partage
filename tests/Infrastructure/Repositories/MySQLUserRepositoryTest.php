<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../../vendor/autoload.php';

class MySQLUserRepositoryTest extends TestCase
{
    private MockObject $pdo;
    private MySQLUserRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        
        // Inject mock PDO into MySQLFactory
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);

        $this->repository = new MySQLUserRepository();
    }

    protected function tearDown(): void
    {
        // Reset MySQLFactory
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testFindByEmailFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with(['john@example.com']);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'name' => 'Doe',
            'forename' => 'John',
            'email' => 'john@example.com',
            'password_hash' => 'hash',
            'user_type' => 'customer'
        ]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM users WHERE email = ? LIMIT 1")
            ->willReturn($stmt);

        $user = $this->repository->findByEmail('john@example.com');

        $this->assertNotNull($user);
        $this->assertInstanceOf(Customer::class, $user);
        $this->assertEquals('john@example.com', $user->getEmail());
    }

    public function testExistsByEmail(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->with(['john@example.com']);
        $stmt->method('fetchColumn')->willReturn(1);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with("SELECT COUNT(*) FROM users WHERE email = ?")
            ->willReturn($stmt);

        $exists = $this->repository->existsByEmail('john@example.com');

        $this->assertTrue($exists);
    }

    public function testSaveInsertCustomer(): void
    {
        $user = new Customer('Doe', 'John', 'john@example.com', 'hash');
        
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO users'))
            ->willReturn($stmt);
            
        $this->pdo->method('lastInsertId')->willReturn('10');

        $this->repository->save($user);

        $this->assertEquals(10, $user->getId());
    }

    public function testSaveInsertOwner(): void
    {
        $user = new Owner('Doe', 'Jane', 'jane@example.com', 'hash');
        
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO parking_owners'))
            ->willReturn($stmt);
            
        $this->pdo->method('lastInsertId')->willReturn('20');

        $this->repository->save($user);

        $this->assertEquals(20, $user->getId());
    }
}
