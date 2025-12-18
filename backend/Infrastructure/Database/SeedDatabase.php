<?php
require_once __DIR__ . '/Factories/MySQLFactory.php';
require_once __DIR__ . '/../../Functions/autoloader.php';

class SeedDatabase {
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo;
    }

    public function run(): void {
        $pdo = $this->pdo ?? MySQLFactory::getConnection();
        
        echo "🌱 Seeding database with test data...\n\n";
        
        // Désactiver temporairement les contraintes de clés étrangères
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Vider les tables existantes
        $this->truncateTables($pdo);
        
        // Réactiver les contraintes
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Insérer les données dans l'ordre des dépendances
        $this->seedParkingOwners($pdo);
        $this->seedUsers($pdo);
        $this->seedParkings($pdo);
        $this->seedParkingTiers($pdo);
        $this->seedPricingTiers($pdo);
        $this->seedReservations($pdo);
        $this->seedParkingSessions($pdo);
        $this->seedSubscriptionTypes($pdo);
        $this->seedSubscriptionTimeSlots($pdo);
        $this->seedUserSubscriptions($pdo);
        $this->seedInvoices($pdo);
        
        echo "\n✅ Database seeded successfully!\n";
    }

    private function truncateTables(PDO $pdo): void {
        $tables = [
            'invoices',
            'user_subscriptions',
            'subscription_time_slots', 
            'subscription_types',
            'parkings_sessions',
            'reservations',
            'pricing_tiers',
            'parking_tiers',
            'parkings',
            'users',
            'parking_owners'
        ];
        
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE $table");
        }
        echo "✓ Tables truncated\n";
    }

    private function seedParkingOwners(PDO $pdo): void {
        $owners = [
            ['Jean', 'Dupont', 'jean.dupont@parking.fr'],
            ['Marie', 'Martin', 'marie.martin@parking.fr'],
            ['Pierre', 'Bernard', 'pierre.bernard@parking.fr'],
            ['Sophie', 'Dubois', 'sophie.dubois@parking.fr'],
            ['Lucas', 'Thomas', 'lucas.thomas@parking.fr'],
            ['Emma', 'Robert', 'emma.robert@parking.fr'],
            ['Hugo', 'Richard', 'hugo.richard@parking.fr'],
            ['Léa', 'Petit', 'lea.petit@parking.fr'],
            ['Louis', 'Durand', 'louis.durand@parking.fr'],
            ['Chloé', 'Leroy', 'chloe.leroy@parking.fr'],
            ['Gabriel', 'Moreau', 'gabriel.moreau@parking.fr'],
            ['Manon', 'Simon', 'manon.simon@parking.fr'],
            ['Raphaël', 'Laurent', 'raphael.laurent@parking.fr'],
            ['Camille', 'Lefebvre', 'camille.lefebvre@parking.fr'],
            ['Arthur', 'Michel', 'arthur.michel@parking.fr'],
            ['Sarah', 'Garcia', 'sarah.garcia@parking.fr'],
            ['Nathan', 'David', 'nathan.david@parking.fr'],
            ['Inès', 'Bertrand', 'ines.bertrand@parking.fr'],
            ['Théo', 'Roux', 'theo.roux@parking.fr'],
            ['Jade', 'Vincent', 'jade.vincent@parking.fr'],
            ['Paul', 'Girard', 'paul.girard@parking.fr'],
            ['Julie', 'Fontaine', 'julie.fontaine@parking.fr'],
            ['Antoine', 'Lemoine', 'antoine.lemoine@parking.fr'],
            ['Sabrina', 'Faure', 'sabrina.faure@parking.fr'],
            ['Olivier', 'Perrot', 'olivier.perrot@parking.fr'],
            ['Céline', 'Marchand', 'celine.marchand@parking.fr'],
            ['Vincent', 'Garnier', 'vincent.garnier@parking.fr'],
            ['Amandine', 'Chevalier', 'amandine.chevalier@parking.fr'],
            ['Florent', 'Barbier', 'florent.barbier@parking.fr'],
            ['Elodie', 'Renaud', 'elodie.renaud@parking.fr'],
            ['Guillaume', 'Benoit', 'guillaume.benoit@parking.fr'],
            ['Aurélie', 'Paris', 'aurelie.paris@parking.fr'],
            ['Benoît', 'Muller', 'benoit.muller@parking.fr'],
            ['Sandrine', 'Leclerc', 'sandrine.leclerc@parking.fr'],
            ['Damien', 'Lopez', 'damien.lopez@parking.fr'],
            ['Mickael', 'Dupuis', 'mickael.dupuis@parking.fr'],
            ['Sonia', 'Morin', 'sonia.morin@parking.fr'],
            ['Patrice', 'Guerin', 'patrice.guerin@parking.fr'],
            ['Isabelle', 'Lemoine', 'isabelle.lemoine@parking.fr'],
            ['Alexandre', 'Renard', 'alexandre.renard@parking.fr'],
            ['Catherine', 'Roy', 'catherine.roy@parking.fr']
        ];

        $stmt = $pdo->prepare("INSERT INTO parking_owners (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        foreach ($owners as $owner) {
            $stmt->execute([$owner[0], $owner[1], $owner[2], $hashedPassword]);
        }
        echo "✓ 40 parking owners created\n";
    }

    private function seedUsers(PDO $pdo): void {
        $users = [
            // admin
            ['Admin', 'System', 'admin@taxawcar.com', 'admin'],
            // users
            ['Alice', 'Blanc', 'alice.blanc@email.com', 'user'],
            ['Bob', 'Noir', 'bob.noir@email.com', 'user'],
            ['Claire', 'Rouge', 'claire.rouge@email.com', 'user'],
            ['David', 'Vert', 'david.vert@email.com', 'user'],
            ['Émilie', 'Bleu', 'emilie.bleu@email.com', 'user'],
            ['François', 'Jaune', 'francois.jaune@email.com', 'user'],
            ['Gaëlle', 'Orange', 'gaelle.orange@email.com', 'user'],
            ['Henri', 'Violet', 'henri.violet@email.com', 'user'],
            ['Isabelle', 'Rose', 'isabelle.rose@email.com', 'user'],
            ['Jacques', 'Gris', 'jacques.gris@email.com', 'user'],
            ['Karine', 'Marron', 'karine.marron@email.com', 'user'],
            ['Laurent', 'Beige', 'laurent.beige@email.com', 'user'],
            ['Mélanie', 'Turquoise', 'melanie.turquoise@email.com', 'user'],
            ['Nicolas', 'Indigo', 'nicolas.indigo@email.com', 'user'],
            ['Olivia', 'Corail', 'olivia.corail@email.com', 'user'],
            ['Patrick', 'Lavande', 'patrick.lavande@email.com', 'user'],
            ['Quentin', 'Menthe', 'quentin.menthe@email.com', 'user'],
            ['Rachel', 'Saumon', 'rachel.saumon@email.com', 'user'],
            ['Sébastien', 'Olive', 'sebastien.olive@email.com', 'user'],
            ['Tiphaine', 'Cyan', 'tiphaine.cyan@email.com', 'user']
        ];

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash('user123', PASSWORD_DEFAULT);
        $hashedAdmin = password_hash('admin123', PASSWORD_DEFAULT);
        
        foreach ($users as $user) {
            $pass = ($user[3] === 'admin') ? $hashedAdmin : $hashedPassword;
            $stmt->execute([$user[0], $user[1], $user[2], $pass, $user[3]]);
        }
        echo "✓ 21 users (customers/admin) created\n";
    }

    private function seedParkings(PDO $pdo): void {
        // Générer 40 parkings pour 40 propriétaires
        $cities = [
            ['Paris', 48.8566, 2.3522], ['Marseille', 43.2965, 5.3698], ['Lyon', 45.7640, 4.8357], ['Toulouse', 43.6047, 1.4442],
            ['Bordeaux', 44.8378, -0.5792], ['Nantes', 47.2184, -1.5536], ['Rennes', 48.1173, -1.6778], ['Nice', 43.7102, 7.2620],
            ['Strasbourg', 48.5734, 7.7521], ['Montpellier', 43.6108, 3.8767], ['Lille', 50.6292, 3.0573], ['Dijon', 47.3220, 5.0415],
            ['Rouen', 49.4432, 1.0999], ['Angers', 47.4784, -0.5632], ['Brest', 48.3904, -4.4861], ['Grenoble', 45.1885, 5.7245],
            ['Caen', 49.1829, -0.3707], ['Le Havre', 49.4944, 0.1079], ['Saint-Étienne', 45.4397, 4.3872], ['Toulon', 43.1242, 5.9280],
        ];
        $parkings = [];
        for ($i = 1; $i <= 40; $i++) {
            $cityIdx = ($i - 1) % count($cities);
            $city = $cities[$cityIdx][0];
            $lat = $cities[$cityIdx][1] + (mt_rand(-100, 100) / 10000);
            $lng = $cities[$cityIdx][2] + (mt_rand(-100, 100) / 10000);
            $name = "$city Parking " . chr(65 + (($i - 1) % 26));
            $address = ($i * 3) . " Rue Principale";
            $totalSpaces = 60 + ($i * 3) % 150;
            $hourlyRate = round(2.0 + ($i % 10) * 0.35, 2);
            $parkings[] = [$i, $name, $address, $city, $lat, $lng, $totalSpaces, $hourlyRate];
        }
        $stmt = $pdo->prepare("INSERT INTO parkings (owner_id, name, address, city, latitude, longitude, total_spaces, hourly_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($parkings as $parking) {
            $stmt->execute($parking);
        }
        echo "✓ 40 parkings created\n";
    }

    private function seedParkingTiers(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO parking_tiers (parking_id, day_of_week, open_time, close_time, is_24h) VALUES (?, ?, ?, ?, ?)");
        
        for ($parkingId = 1; $parkingId <= 20; $parkingId++) {
            // Parkings 24h/24 : centres-villes majeurs (1, 5, 10, 15)
            $is24h = in_array($parkingId, [1, 5, 10, 15]);
            
            for ($day = 0; $day <= 6; $day++) {
                if ($is24h) {
                    $stmt->execute([$parkingId, $day, '00:00:00', '23:59:59', 1]);
                } else {
                    if ($day == 0) { // Dimanche
                        $stmt->execute([$parkingId, $day, '10:00:00', '20:00:00', 0]);
                    } elseif ($day == 6) { // Samedi
                        $stmt->execute([$parkingId, $day, '08:00:00', '22:00:00', 0]);
                    } else { // Lundi-Vendredi
                        $stmt->execute([$parkingId, $day, '06:00:00', '23:00:00', 0]);
                    }
                }
            }
        }
        echo "✓ 140 parking tiers (opening hours) created\n";
    }

    private function seedPricingTiers(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO pricing_tiers (parking_id, time, price) VALUES (?, ?, ?)");
        
        // Grilles tarifaires basées sur l'heure de la journée (comme l'entité PricingTier)
        // Prix différents selon les heures (tarif de jour, tarif de nuit, heures de pointe)
        for ($parkingId = 1; $parkingId <= 20; $parkingId++) {
            $basePrice = 2.00 + ($parkingId * 0.15); // Prix de base variable par parking
            
            // Tarif nuit (00:00-06:00) - moins cher
            $stmt->execute([$parkingId, '00:00:00', round($basePrice * 0.5, 2)]);
            
            // Tarif matin tôt (06:00-08:00)
            $stmt->execute([$parkingId, '06:00:00', round($basePrice * 0.8, 2)]);
            
            // Heures de pointe matin (08:00-10:00) - plus cher
            $stmt->execute([$parkingId, '08:00:00', round($basePrice * 1.3, 2)]);
            
            // Tarif journée (10:00-12:00)
            $stmt->execute([$parkingId, '10:00:00', round($basePrice, 2)]);
            
            // Pause déjeuner (12:00-14:00) - légèrement plus cher
            $stmt->execute([$parkingId, '12:00:00', round($basePrice * 1.1, 2)]);
            
            // Après-midi (14:00-17:00)
            $stmt->execute([$parkingId, '14:00:00', round($basePrice, 2)]);
            
            // Heures de pointe soir (17:00-20:00) - plus cher
            $stmt->execute([$parkingId, '17:00:00', round($basePrice * 1.4, 2)]);
            
            // Soirée (20:00-00:00)
            $stmt->execute([$parkingId, '20:00:00', round($basePrice * 0.7, 2)]);
        }
        echo "✓ 160 pricing tiers (time-based pricing) created\n";
    }

    private function seedReservations(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, parking_id, start_time, end_time, total_price, penalty_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $statuses = ['pending', 'active', 'completed', 'completed', 'completed', 'cancelled'];
        
        for ($i = 0; $i < 20; $i++) {
            $userId = ($i % 20) + 1;
            $parkingId = (($i * 3) % 20) + 1;
            
            // Dates de réservation réparties sur les 30 derniers jours et 10 prochains jours
            $daysOffset = $i - 10; // De -10 à +9 jours
            $startDate = new DateTime();
            $startDate->modify("{$daysOffset} days");
            $startHour = 8 + ($i % 10); // Entre 8h et 17h
            $startDate->setTime($startHour, 0);
            
            $endDate = clone $startDate;
            $duration = [1, 2, 2, 3, 4, 6][$i % 6]; // Durées variées
            $endDate->modify("+{$duration} hours");
            
            // Prix basé sur la formule de l'entité Reservation: 10 + (heures * 2)
            $totalPrice = 10 + ($duration * 2);
            
            // Pénalité pour certaines réservations (dépassement)
            $penalty = ($i % 5 == 0) ? round($totalPrice * 0.25, 2) : 0;
            
            // Statut cohérent avec les dates
            if ($daysOffset < -2) {
                $status = 'completed';
            } elseif ($daysOffset < 0) {
                $status = ($i % 3 == 0) ? 'cancelled' : 'completed';
            } elseif ($daysOffset == 0) {
                $status = 'active';
            } else {
                $status = 'pending';
            }
            
            $stmt->execute([
                $userId,
                $parkingId,
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s'),
                $totalPrice,
                $penalty,
                $status
            ]);
        }
        echo "✓ 20 reservations created\n";
    }

    private function seedParkingSessions(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO parkings_sessions (user_id, parking_id, reservation_id, entry_time, exit_time, is_overstay) VALUES (?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < 20; $i++) {
            $userId = ($i % 20) + 1;
            $parkingId = (($i * 2) % 20) + 1;
            $reservationId = ($i < 12) ? $i + 1 : null;
            $daysOffset = $i - 8;
            $entryDate = new DateTime();
            $entryDate->modify("{$daysOffset} days");
            $entryDate->setTime(9 + ($i % 8), 15 * ($i % 4));
            $exitDate = null;
            if ($i < 16) {
                $exitDate = clone $entryDate;
                $duration = [1, 2, 2, 3, 4, 5][$i % 6];
                $exitDate->modify("+{$duration} hours");
                $exitDate->modify("+" . (5 * ($i % 10)) . " minutes");
            }
            $isOverstay = ($i % 6 == 0 && $reservationId !== null) ? 1 : 0;
            $stmt->execute([
                $userId,
                $parkingId,
                $reservationId,
                $entryDate->format('Y-m-d H:i:s'),
                $exitDate ? $exitDate->format('Y-m-d H:i:s') : null,
                $isOverstay
            ]);
        }
        echo "✓ 20 parking sessions created\n";
    }

    private function seedSubscriptionTypes(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO subscription_types (parking_id, name, description, monthly_price, duration_months) VALUES (?, ?, ?, ?, ?)");
        
        // Types d'abonnements avec durées cohérentes avec l'entité SubscriptionType
        $types = [
            ['Journée', 'Accès du lundi au vendredi, 8h-18h', 75.00, 1],
            ['Soirée', 'Accès tous les jours, 18h-8h', 45.00, 1],
            ['Weekend', 'Accès samedi et dimanche, toute la journée', 35.00, 1],
            ['Illimité', 'Accès 24h/24, 7j/7', 140.00, 1],
            ['Étudiant', 'Tarif réduit, lundi au vendredi, 7h-20h', 40.00, 3],
            ['Annuel', 'Abonnement annuel illimité', 120.00, 12],
            ['Semestre', 'Abonnement 6 mois illimité', 130.00, 6],
            ['Nuit', 'Accès de 20h à 8h tous les jours', 50.00, 1],
        ];
        
        $count = 0;
        // Créer des abonnements pour les 5 premiers parkings (4 types chacun)
        for ($parkingId = 1; $parkingId <= 5; $parkingId++) {
            foreach ($types as $index => $type) {
                if ($count >= 20) break 2;
                
                // Ajuster le prix selon le parking (centres-villes plus chers)
                $priceMultiplier = 1 + (($parkingId - 1) * 0.08);
                $adjustedPrice = round($type[2] * $priceMultiplier, 2);
                
                $stmt->execute([
                    $parkingId, 
                    $type[0], 
                    $type[1], 
                    $adjustedPrice,
                    $type[3]
                ]);
                $count++;
            }
        }
        echo "✓ 20 subscription types created\n";
    }

    private function seedSubscriptionTimeSlots(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO subscription_time_slots (subscription_type_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        
        // Définir les créneaux selon le type d'abonnement (cohérent avec weeklyTimeSlots de l'entité)
        $slotConfigs = [
            // Journée (IDs 1, 9, 17)
            'Journée' => ['days' => [1,2,3,4,5], 'start' => '08:00:00', 'end' => '18:00:00'],
            // Soirée (IDs 2, 10, 18)
            'Soirée' => ['days' => [0,1,2,3,4,5,6], 'start' => '18:00:00', 'end' => '23:59:59'],
            // Weekend (IDs 3, 11, 19)
            'Weekend' => ['days' => [0,6], 'start' => '00:00:00', 'end' => '23:59:59'],
            // Illimité (IDs 4, 12, 20)
            'Illimité' => ['days' => [0,1,2,3,4,5,6], 'start' => '00:00:00', 'end' => '23:59:59'],
            // Étudiant (IDs 5, 13)
            'Étudiant' => ['days' => [1,2,3,4,5], 'start' => '07:00:00', 'end' => '20:00:00'],
            // Annuel (IDs 6, 14)
            'Annuel' => ['days' => [0,1,2,3,4,5,6], 'start' => '00:00:00', 'end' => '23:59:59'],
            // Semestre (IDs 7, 15)
            'Semestre' => ['days' => [0,1,2,3,4,5,6], 'start' => '00:00:00', 'end' => '23:59:59'],
            // Nuit (IDs 8, 16)
            'Nuit' => ['days' => [0,1,2,3,4,5,6], 'start' => '20:00:00', 'end' => '23:59:59'],
        ];
        
        $typeNames = ['Journée', 'Soirée', 'Weekend', 'Illimité', 'Étudiant', 'Annuel', 'Semestre', 'Nuit'];
        
        $count = 0;
        for ($subTypeId = 1; $subTypeId <= 20; $subTypeId++) {
            $typeName = $typeNames[($subTypeId - 1) % 8];
            $config = $slotConfigs[$typeName];
            
            foreach ($config['days'] as $day) {
                $stmt->execute([$subTypeId, $day, $config['start'], $config['end']]);
                $count++;
            }
            
            // Pour les abonnements "Nuit", ajouter aussi le créneau du matin (00:00-08:00)
            if ($typeName === 'Nuit') {
                foreach ($config['days'] as $day) {
                    $stmt->execute([$subTypeId, $day, '00:00:00', '08:00:00']);
                    $count++;
                }
            }
        }
        echo "✓ $count subscription time slots created\n";
    }

    private function seedUserSubscriptions(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO user_subscriptions (user_id, subscription_type_id, start_date, end_date, duration_months, status) VALUES (?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < 20; $i++) {
            $userId = ($i % 20) + 1;
            $subscriptionTypeId = ($i % 20) + 1;
            
            // Durées cohérentes avec l'entité Subscription (1-12 mois)
            $durations = [1, 1, 1, 3, 3, 6, 6, 12, 1, 1];
            $durationMonths = $durations[$i % 10];
            
            // Dates variées
            $startDate = new DateTime();
            $monthsAgo = ($i % 6);
            $startDate->modify("-{$monthsAgo} months");
            $startDate->modify("-" . ($i * 3) . " days");
            
            $endDate = clone $startDate;
            $endDate->modify("+{$durationMonths} months");
            
            // Statut basé sur les dates
            $now = new DateTime();
            if ($endDate < $now) {
                $status = ($i % 3 == 0) ? 'cancelled' : 'expired';
            } else {
                $status = 'active';
            }
            
            $stmt->execute([
                $userId,
                $subscriptionTypeId,
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $durationMonths,
                $status
            ]);
        }
        echo "✓ 20 user subscriptions created\n";
    }

    private function seedInvoices(PDO $pdo): void {
        $stmt = $pdo->prepare("INSERT INTO invoices (reservation_id, amount, currency, generated_at) VALUES (?, ?, ?, ?)");
        
        // Créer des factures pour les réservations complétées (cohérent avec l'entité Invoice)
        for ($i = 0; $i < 15; $i++) {
            $reservationId = $i + 1;
            
            // Montant basé sur la formule de l'entité: base + (heures * tarif)
            $durations = [1, 2, 2, 3, 4, 6];
            $duration = $durations[$i % 6];
            $amount = 10 + ($duration * 2); // Même calcul que getAmount() dans Reservation
            
            // Ajouter la pénalité si applicable
            if ($i % 5 == 0) {
                $amount += round($amount * 0.25, 2);
            }
            
            $generatedAt = new DateTime();
            $generatedAt->modify("-" . (15 - $i) . " days");
            $generatedAt->modify("+" . rand(0, 4) . " hours"); // Variation réaliste
            
            $stmt->execute([
                $reservationId,
                round($amount, 2),
                'EUR',
                $generatedAt->format('Y-m-d H:i:s')
            ]);
        }
        echo "✓ 15 invoices created\n";
    }
}

// Run if executed directly (not included)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    try {
        $seed = new SeedDatabase();
        $seed->run();
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
