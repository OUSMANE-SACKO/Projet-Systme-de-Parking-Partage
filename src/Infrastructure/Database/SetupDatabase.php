<?php
require_once __DIR__ . '/Factories/MySQLFactory.php';
require_once __DIR__ . '/../../Functions/load_env.php';

try {
    $pdo = MySQLFactory::getConnection();
    
    echo "Creating tables...\n";
    
    // Table des propriétaires de parking
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parking_owners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table parking_owners created\n";
    
    // Table des utilisateurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table users created\n";
    
    // Table des parkings
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parkings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            total_spaces INT NOT NULL,
            hourly_rate DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES parking_owners(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table parkings created\n";
    
    // Table des horaires d'ouverture (un parking peut avoir plusieurs plages)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parking_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parking_id INT NOT NULL,
            day_of_week TINYINT NOT NULL COMMENT '0=Dimanche, 1=Lundi...6=Samedi',
            open_time TIME NOT NULL,
            close_time TIME NOT NULL,
            is_24h BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table parking_schedules created\n";
    
    // Table des grilles tarifaires (tarifs progressifs)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pricing_tiers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parking_id INT NOT NULL,
            duration_minutes INT NOT NULL COMMENT 'Durée en minutes (par tranches de 15)',
            rate DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table pricing_tiers created\n";
    
    // Table des réservations
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            parking_id INT NOT NULL,
            start_time TIMESTAMP NOT NULL,
            end_time TIMESTAMP NOT NULL,
            total_price DECIMAL(10, 2),
            penalty_amount DECIMAL(10, 2) DEFAULT 0,
            status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE,
            INDEX idx_reservation_times (parking_id, start_time, end_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table reservations created\n";
    
    // Table des stationnements (entrée/sortie réelles)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS parkings_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            parking_id INT NOT NULL,
            reservation_id INT,
            entry_time TIMESTAMP NOT NULL,
            exit_time TIMESTAMP NULL,
            is_overstay BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE,
            FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
            INDEX idx_active_sessions (parking_id, exit_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table parkings_sessions created\n";
    
    // Table des types d'abonnements
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscription_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parking_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            monthly_price DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (parking_id) REFERENCES parkings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table subscription_types created\n";
    
    // Table des créneaux d'abonnements (les horaires disponibles pour un type d'abo)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscription_time_slots (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subscription_type_id INT NOT NULL,
            day_of_week TINYINT NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            FOREIGN KEY (subscription_type_id) REFERENCES subscription_types(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table subscription_time_slots created\n";
    
    // Table des abonnements actifs des utilisateurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subscription_type_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (subscription_type_id) REFERENCES subscription_types(id) ON DELETE CASCADE,
            INDEX idx_active_subscriptions (subscription_type_id, status, start_date, end_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✓ Table user_subscriptions created\n";
    
    echo "\n✅ All tables created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>