<?php

require_once __DIR__ . '/Factories/MySQLFactory.php';

// use Infrastructure\Database\Factories\MySQLFactory;

try {
    $pdo = MySQLFactory::getConnection();
    echo "✅ Connection to MySQL successful!\n";
    
    // Test simple
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $result = $stmt->fetch();
    echo "Connected to database: " . $result['db'] . "\n";
    
    // Afficher les tables existantes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "\n⚠️  No tables found. Run SetupDatabase.php to create them.\n";
    } else {
        echo "\nExisting tables:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}