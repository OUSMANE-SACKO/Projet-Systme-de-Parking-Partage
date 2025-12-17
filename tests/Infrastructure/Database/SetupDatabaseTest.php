<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../../vendor/autoload.php';

class SetupDatabaseTest extends TestCase
{
    private MockObject $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        
        // Inject mock PDO into MySQLFactory
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $this->pdo);
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testRunCreatesTables(): void
    {
        // Expect multiple exec calls for table creation
        $this->pdo->expects($this->atLeastOnce())
            ->method('exec')
            ->with($this->stringContains('CREATE TABLE'));

        $setup = new SetupDatabase();
        
        // Capture output to avoid cluttering test output
        ob_start();
        $setup->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Creating tables...', $output);
        $this->assertStringContainsString('All tables created successfully!', $output);
    }

    public function testRunHandlesException(): void
    {
        $this->pdo->method('exec')->willThrowException(new PDOException('Connection failed'));

        $setup = new SetupDatabase();
        
        $this->expectException(PDOException::class);
        
        ob_start();
        try {
            $setup->run();
        } finally {
            ob_end_clean();
        }
    }
}
