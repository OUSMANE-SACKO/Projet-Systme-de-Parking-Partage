<?php
    require_once __DIR__ . '/Factories/MySQLFactory.php';
    require_once __DIR__ . '/Factories/MongoDBFactory.php';
    require_once __DIR__ . '/Factories/PostgreSQLFactory.php';
    require_once __DIR__ . '/Factories/SQLiteFactory.php';

    class DatabaseManager {
        public static function getMySQL(): PDO {
            return MySQLFactory::getConnection();
        }
        
        public static function getMongo(): MongoDB\Driver\Manager {
            return MongoDBFactory::getConnection();
        }
        
        public static function closeAll(): void {
            MySQLFactory::closeConnection();
            MongoDBFactory::closeConnection();
        }
    }
?>