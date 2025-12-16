<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../vendor/autoload.php';

class MySQLFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testGetConnectionReturnsSingleton(): void
    {
        // Inject a mock to avoid real connection attempt
        $mockPDO = $this->createMock(PDO::class);
        
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $mockPDO);

        $conn1 = MySQLFactory::getConnection();
        $conn2 = MySQLFactory::getConnection();

        $this->assertSame($mockPDO, $conn1);
        $this->assertSame($conn1, $conn2);
    }

    public function testCloseConnection(): void
    {
        $mockPDO = $this->createMock(PDO::class);
        
        $reflection = new ReflectionClass(MySQLFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $mockPDO);

        MySQLFactory::closeConnection();

        $this->assertNull($property->getValue());
    }
}
