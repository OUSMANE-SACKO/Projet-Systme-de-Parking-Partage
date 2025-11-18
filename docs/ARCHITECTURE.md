# Architecture Clean & SOLID

## Structure du Projet

```
src/
├── Domain/                          # Couche Domain (Clean Architecture)
│   ├── Entities/                    # Entités métier
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── Owner.php
│   │   ├── Parking.php
│   │   ├── Reservation.php
│   │   ├── Invoice.php
│   │   └── ...
│   ├── Repositories/                # Interfaces de repositories (abstractions)
│   │   ├── IUserRepository.php
│   │   ├── IParkingRepository.php
│   │   └── IReservationRepository.php
│   └── Services/                    # Interfaces de services
│       └── IPasswordHasher.php
│
├── Application/                     # Couche Application (Use Cases)
│   └── UseCases/
│       ├── RegisterCustomerUseCase.php
│       ├── RegisterOwnerUseCase.php
│       ├── AuthenticateUserUseCase.php
│       ├── SearchAvailableParkingsUseCase.php
│       └── ...
│
└── Infrastructure/                  # Couche Infrastructure (implémentations)
    ├── Repositories/                # Implémentations concrètes
    │   ├── MySQLUserRepository.php
    │   └── MySQLParkingRepository.php
    ├── Security/
    │   └── PasswordHasher.php
    └── Database/
        └── DatabaseManager.php
```

## Principes SOLID Appliqués

### S - Single Responsibility Principle
Chaque classe a une seule responsabilité:
- `User` gère uniquement les données utilisateur
- `IUserRepository` définit les opérations de persistance
- `RegisterCustomerUseCase` gère uniquement l'inscription
- `PasswordHasher` gère uniquement le hashing

### O - Open/Closed Principle
Le code est ouvert à l'extension, fermé à la modification:
- Ajouter une nouvelle BDD = créer une nouvelle implémentation de repository
- Pas besoin de modifier les use cases existants

### L - Liskov Substitution Principle
Les implémentations sont substituables:
- `MySQLUserRepository` peut être remplacé par `MongoDBUserRepository`
- Les use cases fonctionnent avec n'importe quelle implémentation

### I - Interface Segregation Principle
Interfaces spécifiques aux besoins:
- `IUserRepository` pour les opérations utilisateur
- `IParkingRepository` pour les opérations parking
- `IPasswordHasher` pour le hashing uniquement

### D - Dependency Inversion Principle
Dépendre des abstractions, pas des implémentations:
- Use cases dépendent de `IUserRepository`, pas de `MySQLUserRepository`
- Injection de dépendances via constructeur

## Injection de Dépendances

### Avant (couplage fort):
```php
class RegisterCustomerUseCase {
    public function execute($name, $email, $password, array $existingUsers) {
        // Logique directe avec tableau en mémoire
        foreach ($existingUsers as $user) { ... }
        
        // Création directe de dépendance
        $hashUseCase = new HashPasswordUseCase();
    }
}
```

### Après (couplage faible):
```php
class RegisterCustomerUseCase {
    private IUserRepository $userRepository;
    private IPasswordHasher $passwordHasher;

    public function __construct(IUserRepository $userRepository, IPasswordHasher $passwordHasher) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function execute($name, $email, $password): Customer {
        // Utilise les abstractions
        if ($this->userRepository->existsByEmail($email)) { ... }
        $hash = $this->passwordHasher->hash($password);
    }
}
```

## Utilisation

```php
// Configuration
$userRepository = new MySQLUserRepository($dbManager);
$passwordHasher = new PasswordHasher(getenv('PEPPER'));

// Injection de dépendances
$useCase = new RegisterCustomerUseCase($userRepository, $passwordHasher);

// Exécution
$customer = $useCase->execute('Dupont', 'Jean', 'email@test.com', 'password123');
```

## Avantages

1. **Testabilité**: Mock facile des dépendances pour tests unitaires
2. **Maintenabilité**: Changement de BDD sans toucher à la logique métier
3. **Évolutivité**: Ajout de nouvelles implémentations sans modification
4. **Découplage**: Domain ne dépend pas de l'Infrastructure
5. **Flexibilité**: Configuration différente par environnement

## Requêtes BDD

Le hash du mot de passe est récupéré automatiquement via:
```php
$user = $userRepository->findByEmail($email);
$hash = $user->getPasswordHash(); // Récupéré de la BDD
```

Pas besoin de passer le hash manuellement, le repository s'en charge!
