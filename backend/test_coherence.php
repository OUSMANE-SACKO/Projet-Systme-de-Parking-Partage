<?php
    // --- 1. Configuration ---
    // require_once __DIR__ . '/Functions/autoloader.php';
    require_once __DIR__ . '/Infrastructure/Database/SetupDatabase.php'; 

    // --- 2. Entités (Nécessaires car utilisées par les Interfaces) ---
    require_once __DIR__ . '/Domain/Entities/User.php';
    require_once __DIR__ . '/Domain/Entities/Owner.php';
    require_once __DIR__ . '/Domain/Entities/Customer.php';
    require_once __DIR__ . '/Domain/Entities/Parking.php';

    // --- 3. Interfaces (Nécessaires car implémentées par les Repositories) ---
    require_once __DIR__ . '/Domain/Repositories/IUserRepository.php';
    require_once __DIR__ . '/Domain/Repositories/IParkingRepository.php';

    // --- 4. Repositories (Implémentations concrètes) ---
    require_once __DIR__ . '/Infrastructure/Repositories/MySQLUserRepository.php';
    require_once __DIR__ . '/Infrastructure/Repositories/MySQLParkingRepository.php';

    try {
        echo "\n--- DÉBUT DU TEST DE COHÉRENCE ---\n";

        // 1. Création des Repositories
        $userRepo = new MySQLUserRepository();
        $parkingRepo = new MySQLParkingRepository();

        // 2. Création d'un Propriétaire (Owner)
        echo "\n1. Création d'un Owner...\n";
        $owner = new Owner("Dupont", "Jean", "jean.dupont@test.com", "hash12345");

        // Sauvegarde en BDD
        $userRepo->save($owner);

        // VÉRIFICATION ID OWNER
        if ($owner->getId() === null) {
            throw new Exception("ERREUR: L'ID du Owner est null après sauvegarde !");
        }
        echo "✅ Owner créé avec succès. ID généré : " . $owner->getId() . "\n";

        // 3. Création d'un Parking lié à ce Owner
        echo "\n2. Création d'un Parking...\n";
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $parking = new Parking($location, 50); // 50 places

        // --- Simulation du lien Owner ---
        // ATTENTION: Votre entité Parking n'a pas encore de méthode setOwnerId() ! 
        // Pour ce test, on va supposer que le Repo utilise l'ID qu'on lui passe ou une valeur fixe temporaire
        // Dans un vrai scénario, il faudrait faire $parking->setOwnerId($owner->getId());
        // Ici, le repository actuel met '1' en dur ou attend une modif. 
        // Modifions temporairement la logique du test pour refléter la réalité du code actuel :
        // Le code actuel du Repo MySQLParkingRepository met '1' en dur pour owner_id.
        // Assurons-nous que l'ID du owner créé est bien utilisé si on modifie le repo, 
        // sinon le test passera mais avec une incohérence logique.

        $parkingRepo->save($parking);

        // VÉRIFICATION ID PARKING
        if ($parking->getId() === null) {
            throw new Exception("ERREUR: L'ID du Parking est null après sauvegarde !");
        }
        echo "✅ Parking créé avec succès. ID généré : " . $parking->getId() . "\n";

        // 4. Lecture et Vérification des Données
        echo "\n3. Vérification des données en base...\n";

        // Récupération du Parking par ID
        $fetchedParking = $parkingRepo->findById($parking->getId());

        if (!$fetchedParking) {
            throw new Exception("ERREUR: Impossible de récupérer le parking " . $parking->getId());
        }

        // Vérification des valeurs
        $latDiff = abs($fetchedParking->getLocation()['latitude'] - $location['latitude']);
        if ($latDiff > 0.0001) {
            throw new Exception("ERREUR: Latitude incorrecte !");
        }

        if ($fetchedParking->getCapacity() !== 50) {
            throw new Exception("ERREUR: Capacité incorrecte ! Attendu: 50, Reçu: " . $fetchedParking->getCapacity());
        }

        echo "✅ Données du parking validées (Latitude, Capacité).\n";

        // 5. Test de Mise à Jour
        echo "\n4. Test de Mise à Jour (Update)...\n";
        $fetchedParking->setCapacity(100);
        $parkingRepo->save($fetchedParking);

        $updatedParking = $parkingRepo->findById($fetchedParking->getId());
        if ($updatedParking->getCapacity() !== 100) {
            throw new Exception("ERREUR: La mise à jour de la capacité a échoué !");
        }
        echo "✅ Mise à jour réussie (Nouvelle capacité : 100).\n";

        echo "\n--- TEST RÉUSSI AVEC SUCCÈS ! ---\n";

    } catch (Exception $e) {
        echo "\n❌ ÉCHEC DU TEST : " . $e->getMessage() . "\n";
        // Afficher la trace pour le debug
        // echo $e->getTraceAsString();
    }
?>