<?php
require_once __DIR__ . '/DatabaseFactoryInterface.php';

class MongoDBFactory implements DatabaseFactoryInterface {
    private static ?MongoDB\Driver\Manager $instance = null;
    
    private function __construct() {}
    
    public static function getConnection(): MongoDB\Driver\Manager {
        if (self::$instance === null) {
            try {
                $host = getenv('MONGO_HOST') ?: 'localhost';
                $port = getenv('MONGO_PORT') ?: '27017';
                $username = getenv('MONGO_USER') ?: '';
                $password = getenv('MONGO_PASS') ?: '';
                $dbname = getenv('MONGO_NAME') ?: 'parking_system';
                
                if ($username && $password) {
                    $uri = "mongodb://{$username}:{$password}@{$host}:{$port}/{$dbname}";
                } else {
                    $uri = "mongodb://{$host}:{$port}";
                }
                
                self::$instance = new MongoDB\Driver\Manager($uri);
            } catch (Exception $e) {
                throw new RuntimeException("MongoDB connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    public static function closeConnection(): void {
        self::$instance = null;
    }
}
?>