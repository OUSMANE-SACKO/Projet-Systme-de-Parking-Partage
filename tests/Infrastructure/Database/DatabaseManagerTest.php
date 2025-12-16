<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../vendor/autoload.php';

class DatabaseManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset singletons
        $this->resetSingleton(MySQLFactory::class);
    }

    private function resetSingleton(string $className): void
    {
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    private function setSingleton(string $className, object $instance): void
    {
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, $instance);
    }

    public function testGetMySQL(): void
    {
        $mockPDO = $this->createMock(PDO::class);
        $this->setSingleton(MySQLFactory::class, $mockPDO);

        $this->assertSame($mockPDO, DatabaseManager::getMySQL());
    }

    public function testCloseAll(): void
    {
        $mockPDO = $this->createMock(PDO::class);
        $this->setSingleton(MySQLFactory::class, $mockPDO);
        
        DatabaseManager::closeAll();

        // Verify instances are nullified (we need to check via reflection or by calling get and expecting new instance/error)
        // Since we can't easily check "is null" without reflection, let's check via reflection
        
        $reflectionMySQL = new ReflectionClass(MySQLFactory::class);
        $propMySQL = $reflectionMySQL->getProperty('instance');
        $propMySQL->setAccessible(true);
        $this->assertNull($propMySQL->getValue());
    }
}
