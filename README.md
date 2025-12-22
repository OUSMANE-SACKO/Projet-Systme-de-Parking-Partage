# 🅿️ TaxawCar - Système de Gestion de Parking Partagé

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple)
![License](https://img.shields.io/badge/license-MIT-green)

## 📋 À propos du Projet

**TaxawCar** est une plateforme web complète de gestion et de réservation de places de parking partagées. Elle permet aux propriétaires de parking de gérer leurs ressources et aux clients de rechercher et réserver des places en quelques clics.

### ✨ Fonctionnalités Principales

- 🔍 **Recherche de Parkings** : Trouvez des places disponibles par ville ou localisation GPS
- 📍 **Intégration Google Maps** : Visualisez les parkings sur une carte interactive
- 💰 **Tarification Dynamique** : Tarifs horaires variables selon la période
- 📅 **Réservations** : Réservez une place pour la date et l'heure souhaitées
- 🔐 **Authentification Sécurisée** : Inscription et connexion pour clients et propriétaires
- 📊 **Dashboard Propriétaire** : Gestion complète des parkings et revenus
- 🎫 **Abonnements** : Plans d'abonnement mensuel et annuel
- 📜 **Facturation** : Génération de factures PDF pour chaque réservation

---

## 👥 Participants du Projet

| **Ousmane Sacko** 
| **Hugo Martins** 
| **N'DA Abouakou Yann** 
| **Issa Abdoulaye** 

---

## 🏗️ Architecture Technique

### Stack Technologique

```
Frontend:
├── HTML5 / CSS3
├── JavaScript (Vanilla)
└── Google Maps API

Backend:
├── PHP 8.2
├── Architecture Hexagonale
├── PDO MySQL
└── Design Patterns (DTO, UseCase, Repository)

Infrastructure:
├── Docker & Docker Compose
├── MySQL 8.0
├── Apache 2.4
└── PhpMyAdmin

Testing:
├── PHPUnit 11.5
├── Infection (Mutation Testing)
└── 233 Tests (91.4% success rate)
```

### Structure du Projet

```
Projet-Systme-de-Parking-Partage/
├── backend/
│   ├── Application/
│   │   ├── DTO/               # Data Transfer Objects
│   │   └── UseCases/          # Logique métier
│   ├── Domain/
│   │   ├── Entities/          # Entités du domaine
│   │   └── Repositories/      # Interfaces repositories
│   └── Infrastructure/
│       ├── Controller/        # Contrôleurs
│       ├── Database/          # Gestion BD
│       └── Repositories/      # Implémentations
├── frontend/
│   ├── *.html                 # Pages web
│   ├── api.js                 # Client API
│   ├── app.js                 # Logique applicative
│   └── styles.css             # Styles
├── middleware/
│   ├── api.php                # Point d'entrée API
│   └── analyse.php            # Sécurité
├── tests/                      # Suite de tests
├── docker-compose.yml         # Configuration Docker
└── Dockerfile                 # Image PHP
```

---

## 🚀 Installation Rapide

### Prérequis

- Docker & Docker Compose
- PHP 8.2+ (optionnel, Docker gère)
- Composer (inclus dans Docker)

### Étapes d'Installation

```bash
# 1. Cloner le projet
git clone https://github.com/OUSMANE-SACKO/Projet-Systme-de-Parking-Partage.git
cd Projet-Systme-de-Parking-Partage

# 2. Lancer les containers
docker-compose up -d

# 3. Installer les dépendances PHP
docker-compose exec app composer install --ignore-platform-reqs

# 4. Initialiser la base de données
docker-compose exec app php backend/Infrastructure/Database/SetupDatabase.php
docker-compose exec app php backend/Infrastructure/Database/SeedDatabase.php

# 5. Accéder à l'application
# Application: http://localhost:8080
# PhpMyAdmin: http://localhost:8081
# API: http://localhost:8080/middleware/api.php
```
## Exécution rapide

Deux commandes simples (définies comme scripts composer) :

```bash
composer coverage   # Run les tests de couverture
composer open-coverage # Run le résultat sur un fichier html
```

```bash
composer mutation   # runs mutation testing (Infection)
composer open-mutation # runs the html results
```

Sur PowerShell (Windows) :
```powershell
$env:XDEBUG_MODE="coverage"; ./vendor/bin/infection --threads=4 --min-msi=80
```
---

## 💻 Utilisation

### Pour les Clients

1. **Inscription** : Créez un compte client
2. **Connexion** : Accédez à votre dashboard
3. **Rechercher** : Trouvez des parkings disponibles
4. **Réserver** : Sélectionnez les dates et confirmez
5. **Payer** : Recevez une facture

---

## 🧪 Tests et Qualité

### Exécution des Tests

```bash
# Tous les tests
docker-compose exec app php vendor/bin/phpunit tests/

# Avec couverture de code
docker-compose exec app php -dxdebug.mode=coverage vendor/bin/phpunit tests/ --coverage-text

# Tests spécifiques
docker-compose exec app php vendor/bin/phpunit tests/Usecase/
```
Pour implémenter la BDD, il faut appeler les fichiers suivants:

```bash
php SetupDatabase.php # Initialise la base de données
php SeedDatabase.php # Concoie les tables
```

### Résultats des Tests

```
✅ Tests: 233
✅ Assertions: 805
✅ Taux de Réussite: 91.4%
✅ Couverture Estimée: 85-90%
✅ Erreurs/Failures: 22
```

Voir [TEST_REPORT.md](TEST_REPORT.md) pour plus de détails.

---

## 🔧 Configuration

### Variables d'Environnement (.env)

```env
MYSQL_HOST=db
MYSQL_PORT=3306
MYSQL_NAME=parking
MYSQL_USER=user
MYSQL_PASS=password
```

### Ports

| Service | Port | URL |
|---------|------|-----|
| Application | 8080 | http://localhost:8080 |
| PhpMyAdmin | 8081 | http://localhost:8081 |
| MySQL | 7070 | localhost:7070 |

---

## 📊 Base de Données

### Entités Principales

- **Users** : Clients et propriétaires
- **Parkings** : Places de stationnement
- **Reservations** : Réservations de places
- **Sessions** : Entrée/sortie des véhicules
- **Subscriptions** : Abonnements clients
- **Invoices** : Factures
- **PricingTiers** : Tarifs horaires

### Données de Test

- 40 parkings dans 20 villes
- 21 utilisateurs de test
- 140 périodes d'ouverture
- 160 tranches tarifaires
- 20 réservations
- 15 factures

---

## 🔒 Sécurité

- ✅ Protection XSS via CSP headers
- ✅ Protection CSRF via tokens
- ✅ Authentification JWT
- ✅ Validation des entrées
- ✅ Sanitisation des données
- ✅ Cookies HttpOnly & Secure
- ✅ Contrôle d'accès basé sur les rôles (RBAC)

---

## 📚 API Documentation

### Endpoints Principaux

#### Parkings

```bash
# Récupérer tous les parkings
POST /middleware/api.php
Content-Type: application/json
{
  "dtoType": "GetParkingsDTO",
  "city": "Paris"  # optionnel
}

# Rechercher par localisation
POST /middleware/api.php
{
  "dtoType": "SearchParkingsDTO",
  "latitude": 48.8566,
  "longitude": 2.3522,
  "radiusKm": 5
}
```

#### Réservations

```bash
# Créer une réservation
POST /middleware/api.php
{
  "dtoType": "ReserveParkingDTO",
  "parkingId": 1,
  "startDate": "2025-01-01T10:00:00",
  "endDate": "2025-01-01T12:00:00"
}
```

#### Authentification

```bash
# Connexion
POST /middleware/api.php
{
  "dtoType": "AuthenticateUserDTO",
  "email": "user@example.com",
  "password": "password123"
}

# Inscription
POST /middleware/api.php
{
  "dtoType": "RegisterCustomerDTO",
  "name": "Dupont",
  "forename": "Jean",
  "email": "jean@example.com",
  "password": "password123"
}
```
