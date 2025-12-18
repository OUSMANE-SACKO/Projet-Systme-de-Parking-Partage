<?php
    require_once __DIR__ . '/Factories/MySQLFactory.php';
    // require_once __DIR__ . '/Factories/MongoDBFactory.php';

    class DatabaseManager {
        public static function getMySQL(): PDO {
            return MySQLFactory::getConnection();
        }
    
        
        public static function closeAll(): void {
            MySQLFactory::closeConnection();
        }
    }
?>