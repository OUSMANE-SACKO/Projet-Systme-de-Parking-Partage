<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../../vendor/autoload.php';

class TestConnexionTest extends TestCase
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

    public function testRunSuccessWithTables(): void
    {
        $stmtDb = $this->createMock(PDOStatement::class);
        $stmtDb->method('fetch')->willReturn(['db' => 'test_db']);

        $stmtTables = $this->createMock(PDOStatement::class);
        $stmtTables->method('fetchAll')->willReturn(['table1', 'table2']);

        $this->pdo->expects($this->exactly(2))
            ->method('query')
            ->willReturnMap([
                ['SELECT DATABASE() as db', null, $stmtDb],
                ['SHOW TABLES', null, $stmtTables]
            ]);

        $testConnexion = new TestConnexion();
        
        ob_start();
        $testConnexion->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Connection to MySQL successful!', $output);
        $this->assertStringContainsString('Connected to database: test_db', $output);
        $this->assertStringContainsString('Existing tables:', $output);
        $this->assertStringContainsString('- table1', $output);
    }

    public function testRunSuccessNoTables(): void
    {
        $stmtDb = $this->createMock(PDOStatement::class);
        $stmtDb->method('fetch')->willReturn(['db' => 'test_db']);

        $stmtTables = $this->createMock(PDOStatement::class);
        $stmtTables->method('fetchAll')->willReturn([]);

        $this->pdo->expects($this->exactly(2))
            ->method('query')
            ->willReturnMap([
                ['SELECT DATABASE() as db', null, $stmtDb],
                ['SHOW TABLES', null, $stmtTables]
            ]);

        $testConnexion = new TestConnexion();
        
        ob_start();
        $testConnexion->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('No tables found', $output);
    }

    public function testRunFailure(): void
    {
        $this->pdo->method('query')->willThrowException(new PDOException('Connection failed'));

        $testConnexion = new TestConnexion();
        
        ob_start();
        $testConnexion->run();
        $output = ob_get_clean();

        $this->assertStringContainsString('Connection failed:', $output);
    }
}
