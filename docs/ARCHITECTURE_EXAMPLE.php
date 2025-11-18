<?php
/**
 * Exemple d'utilisation avec l'injection de dépendances selon les principes SOLID.
 * 
 * Cette architecture respecte:
 * - S: Single Responsibility - Chaque classe a une seule raison de changer
 * - O: Open/Closed - Ouvert à l'extension, fermé à la modification
 * - L: Liskov Substitution - Les implémentations peuvent être substituées
 * - I: Interface Segregation - Interfaces spécifiques à chaque besoin
 * - D: Dependency Inversion - Dépendre des abstractions, pas des implémentations
 */

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
    echo "- Parking à " . $result['distance'] . " km: " . 
         $result['availableSpaces'] . " places disponibles\n";
}

// ===== EXEMPLE 4: Inscription d'un propriétaire =====
$registerOwnerUseCase = new RegisterOwnerUseCase($userRepository, $passwordHasher);

try {
    $owner = $registerOwnerUseCase->execute(
        'Martin',
        'Sophie',
        'sophie.martin@example.com',
        'AnotherSecurePass456'
    );
    
    echo "Propriétaire créé avec succès: " . $owner->getId() . "\n";
} catch (InvalidArgumentException $e) {
    echo "Erreur lors de l'inscription: " . $e->getMessage() . "\n";
}

/**
 * AVANTAGES DE CETTE ARCHITECTURE:
 * 
 * 1. TESTABILITÉ: Facile de créer des mocks pour les tests unitaires
 * 2. MAINTENABILITÉ: Changement de BDD = changer uniquement les repositories
 * 3. ÉVOLUTIVITÉ: Ajouter Redis/MongoDB = créer nouvelles implémentations
 * 4. DÉCOUPLAGE: La logique métier ne dépend pas de l'infrastructure
 * 5. FLEXIBILITÉ: Injection différente selon l'environnement (dev/prod)
 */
?>