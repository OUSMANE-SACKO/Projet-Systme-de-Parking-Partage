<?php

// Configuration de la base de données
$dbConfig = [
    'host' => 'localhost',
    'database' => 'parking_system',
    'username' => 'root',
    'password' => ''
];

// Instancier le DatabaseManager
$dbFactory = new MySQLFactory();
$dbManager = $dbFactory->createConnection($dbConfig);

// Instancier les repositories (couche Infrastructure)
$userRepository = new MySQLUserRepository($dbManager);
$parkingRepository = new MySQLParkingRepository($dbManager);

// Instancier les services
$passwordHasher = new PasswordHasher(getenv('PEPPER'));

// ===== EXEMPLE 1: Inscription d'un client =====
$registerCustomerUseCase = new RegisterCustomerUseCase($userRepository, $passwordHasher);

try {
    $customer = $registerCustomerUseCase->execute(
        'Dupont',
        'Jean',
        'jean.dupont@example.com',
        'SecurePassword123'
    );
    
    echo "Client créé avec succès: " . $customer->getId() . "\n";
} catch (InvalidArgumentException $e) {
    echo "Erreur lors de l'inscription: " . $e->getMessage() . "\n";
}

// ===== EXEMPLE 2: Authentification =====
$authenticateUseCase = new AuthenticateUserUseCase($userRepository, $passwordHasher);

$result = $authenticateUseCase->execute('jean.dupont@example.com', 'SecurePassword123');

if ($result['authenticated']) {
    echo "Authentification réussie!\n";
    echo "Bienvenue " . $result['user']->getForename() . " " . $result['user']->getName() . "\n";
    
    // Type-safe: déterminer le type d'utilisateur
    if ($result['user'] instanceof Customer) {
        echo "Type: Client\n";
    } elseif ($result['user'] instanceof Owner) {
        echo "Type: Propriétaire\n";
    }
} else {
    echo "Échec de l'authentification: " . $result['message'] . "\n";
}

// ===== EXEMPLE 3: Recherche de parkings disponibles =====
$searchParkingsUseCase = new SearchAvailableParkingsUseCase($parkingRepository);

$searchResults = $searchParkingsUseCase->execute(
    48.8566,  // latitude Paris
    2.3522,   // longitude Paris
    5.0,      // rayon 5km
    new DateTime()
);

echo "Parkings trouvés: " . $searchResults['count'] . "\n";
foreach ($searchResults['parkings'] as $result) {
    echo "- Parking à " . $result['distance'] . " km: " . $result['availableSpaces'] . " places disponibles\n";
}

?>