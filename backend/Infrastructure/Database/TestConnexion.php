<?php

require_once __DIR__ . '/Factories/MySQLFactory.php';

class TestConnexion {
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo;
    }

    public function run(): void {
        $pdo = $this->pdo ?? MySQLFactory::getConnection();
        
        // Test simple - verify connection works first
        $stmt = $pdo->query("SELECT DATABASE() as db");
        $result = $stmt->fetch();
        
        echo "✅ Connection to MySQL successful!\n";
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
    }
}

// Run if executed directly (not included)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $testConnexion = new TestConnexion();
        $testConnexion->run();
    } catch (Exception $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
    }
}