<?php
    require_once __DIR__ . '/DatabaseFactoryInterface.php';

    class MySQLFactory implements DatabaseFactoryInterface {
        private static ?PDO $instance = null;
        
        private function __construct() {}
        
        public static function getConnection(): PDO {
            if (self::$instance === null) {
                try {
                    $host = getenv('MYSQL_HOST') ?: 'localhost';
                    $dbname = getenv('MYSQL_NAME') ?: 'parking_system';
                    $username = getenv('MYSQL_USER') ?: 'root';
                    $password = getenv('MYSQL_PASS') ?: '';
                    $charset = 'utf8mb4';
                    
                    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
                    
                    self::$instance = new PDO($dsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]);
                } catch (PDOException $e) {
                    throw new RuntimeException("MySQL connection failed: " . $e->getMessage());
                }
            }
            
            return self::$instance;
        }
        
        public static function closeConnection(): void {
            self::$instance = null;
        }
    }
?>