<?php

require_once __DIR__ . '/../../Application/DTO/GetParkingReservationsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetParkingSessionsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetParkingAvailabilityDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetParkingRevenueDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetUnauthorizedDriversDTO.php';
require_once __DIR__ . '/../../Application/DTO/AddSubscriptionTypeDTO.php';
require_once __DIR__ . '/../../Application/DTO/UpdateParkingPricingDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetParkingInfoDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetParkingSubscriptionsDTO.php';
require_once __DIR__ . '/../../Application/DTO/SearchParkingsDTO.php';

class ParkingDataController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getParkingReservations(GetParkingReservationsDTO $dto): array {
        try {
            $sql = "
                SELECT r.*, u.first_name, u.last_name, u.email
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                WHERE r.parking_id = ?
            ";
            $params = [$dto->parkingId];

            if ($dto->status) {
                $sql .= " AND r.status = ?";
                $params[] = $dto->status;
            }

            $sql .= " ORDER BY r.start_time DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'reservations' => array_map(function($r) {
                    return [
                        'id' => $r['id'],
                        'customer' => [
                            'id' => $r['user_id'],
                            'name' => $r['first_name'] . ' ' . $r['last_name'],
                            'email' => $r['email']
                        ],
                        'startTime' => $r['start_time'],
                        'endTime' => $r['end_time'],
                        'status' => $r['status'],
                        'totalPrice' => $r['total_price']
                    ];
                }, $reservations),
                'count' => count($reservations)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getParkingSessions(GetParkingSessionsDTO $dto): array {
        try {
            $sql = "
                SELECT ps.*, u.first_name, u.last_name, u.email
                FROM parkings_sessions ps
                LEFT JOIN users u ON ps.user_id = u.id
                WHERE ps.parking_id = ?
            ";
            $params = [$dto->parkingId];

            if ($dto->activeOnly === 'true') {
                $sql .= " AND ps.exit_time IS NULL";
            }

            $sql .= " ORDER BY ps.entry_time DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'sessions' => array_map(function($s) {
                    return [
                        'id' => $s['id'],
                        'customer' => $s['user_id'] ? [
                            'id' => $s['user_id'],
                            'name' => $s['first_name'] . ' ' . $s['last_name'],
                            'email' => $s['email']
                        ] : null,
                        'entryTime' => $s['entry_time'],
                        'exitTime' => $s['exit_time'],
                        'isActive' => $s['exit_time'] === null
                    ];
                }, $sessions),
                'count' => count($sessions)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getParkingAvailability(GetParkingAvailabilityDTO $dto): array {
        try {
            // Récupérer le parking
            $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = ?");
            $stmt->execute([$dto->parkingId]);
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            $checkTime = $dto->timestamp ? new DateTime($dto->timestamp) : new DateTime();

            // Compter les sessions actives
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM parkings_sessions 
                WHERE parking_id = ? AND exit_time IS NULL
            ");
            $stmt->execute([$dto->parkingId]);
            $occupiedSpaces = (int)$stmt->fetchColumn();

            // Compter les réservations actives
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM reservations 
                WHERE parking_id = ? AND status IN ('pending', 'active')
                AND start_time <= ? AND end_time >= ?
            ");
            $timeStr = $checkTime->format('Y-m-d H:i:s');
            $stmt->execute([$dto->parkingId, $timeStr, $timeStr]);
            $reservedSpaces = (int)$stmt->fetchColumn();

            $totalSpaces = (int)$parking['total_spaces'];
            $availableSpaces = max(0, $totalSpaces - max($occupiedSpaces, $reservedSpaces));

            return [
                'success' => true,
                'parkingId' => $dto->parkingId,
                'checkTime' => $checkTime->format('Y-m-d H:i:s'),
                'totalSpaces' => $totalSpaces,
                'occupiedSpaces' => $occupiedSpaces,
                'reservedSpaces' => $reservedSpaces,
                'availableSpaces' => $availableSpaces
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getParkingRevenue(GetParkingRevenueDTO $dto): array {
        try {
            $monthStart = sprintf('%04d-%02d-01', $dto->year, $dto->month);
            $monthEnd = date('Y-m-t', strtotime($monthStart));

            // Revenus des réservations terminées
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(total_price), 0) as revenue
                FROM reservations 
                WHERE parking_id = ? 
                AND status = 'completed'
                AND end_time >= ? AND end_time <= ?
            ");
            $stmt->execute([$dto->parkingId, $monthStart . ' 00:00:00', $monthEnd . ' 23:59:59']);
            $reservationsRevenue = (float)$stmt->fetchColumn();

            // Revenus des abonnements actifs
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(st.monthly_price), 0) as revenue
                FROM user_subscriptions us
                JOIN subscription_types st ON us.subscription_type_id = st.id
                WHERE st.parking_id = ?
                AND us.status = 'active'
                AND us.start_date <= ? AND us.end_date >= ?
            ");
            $stmt->execute([$dto->parkingId, $monthEnd, $monthStart]);
            $subscriptionsRevenue = (float)$stmt->fetchColumn();

            return [
                'success' => true,
                'month' => sprintf('%04d-%02d', $dto->year, $dto->month),
                'reservationsRevenue' => round($reservationsRevenue, 2),
                'subscriptionsRevenue' => round($subscriptionsRevenue, 2),
                'totalRevenue' => round($reservationsRevenue + $subscriptionsRevenue, 2)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getUnauthorizedDrivers(GetUnauthorizedDriversDTO $dto): array {
        try {
            $checkTime = $dto->timestamp ? new DateTime($dto->timestamp) : new DateTime();
            $timeStr = $checkTime->format('Y-m-d H:i:s');

            // Trouver les sessions actives sans réservation ni abonnement valide
            $stmt = $this->pdo->prepare("
                SELECT ps.*, u.first_name, u.last_name, u.email
                FROM parkings_sessions ps
                JOIN users u ON ps.user_id = u.id
                WHERE ps.parking_id = ? 
                AND ps.exit_time IS NULL
                AND ps.user_id NOT IN (
                    SELECT DISTINCT r.user_id FROM reservations r
                    WHERE r.parking_id = ps.parking_id
                    AND r.start_time <= ? AND r.end_time >= ?
                )
                AND ps.user_id NOT IN (
                    SELECT DISTINCT us.user_id FROM user_subscriptions us
                    JOIN subscription_types st ON us.subscription_type_id = st.id
                    WHERE st.parking_id = ps.parking_id
                    AND us.status = 'active'
                    AND us.start_date <= ? AND us.end_date >= ?
                )
            ");
            $stmt->execute([$dto->parkingId, $timeStr, $timeStr, $timeStr, $timeStr]);
            $unauthorized = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'checkTime' => $timeStr,
                'unauthorizedDrivers' => array_map(function($u) {
                    return [
                        'sessionId' => $u['id'],
                        'customer' => [
                            'id' => $u['user_id'],
                            'name' => $u['first_name'] . ' ' . $u['last_name'],
                            'email' => $u['email']
                        ],
                        'parkedSince' => $u['entry_time']
                    ];
                }, $unauthorized),
                'count' => count($unauthorized)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function addSubscriptionType(AddSubscriptionTypeDTO $dto): array {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO subscription_types (parking_id, name, description, monthly_price, duration_months)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $dto->parkingId,
                $dto->name,
                $dto->description,
                $dto->monthlyPrice,
                $dto->durationMonths
            ]);

            return [
                'success' => true,
                'message' => 'Type d\'abonnement ajouté.',
                'subscriptionType' => [
                    'id' => $this->pdo->lastInsertId(),
                    'name' => $dto->name,
                    'monthlyPrice' => $dto->monthlyPrice,
                    'durationMonths' => $dto->durationMonths
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateParkingPricing(UpdateParkingPricingDTO $dto): array {
        try {
            // Mettre à jour le tarif horaire
            $stmt = $this->pdo->prepare("UPDATE parkings SET hourly_rate = ? WHERE id = ?");
            $stmt->execute([$dto->hourlyRate, $dto->parkingId]);

            // Mettre à jour les grilles tarifaires si fournies
            if ($dto->pricingTiers) {
                $this->pdo->prepare("DELETE FROM pricing_tiers WHERE parking_id = ?")->execute([$dto->parkingId]);
                
                $stmt = $this->pdo->prepare("INSERT INTO pricing_tiers (parking_id, time, price) VALUES (?, ?, ?)");
                foreach ($dto->pricingTiers as $tier) {
                    $stmt->execute([$dto->parkingId, $tier['time'], $tier['price']]);
                }
            }

            return [
                'success' => true,
                'message' => 'Tarifs mis à jour.',
                'hourlyRate' => $dto->hourlyRate
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getParkingInfo(GetParkingInfoDTO $dto): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, po.first_name as owner_first_name, po.last_name as owner_last_name
                FROM parkings p
                JOIN parking_owners po ON p.owner_id = po.id
                WHERE p.id = ?
            ");
            $stmt->execute([$dto->parkingId]);
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            // Récupérer les horaires
            $stmt = $this->pdo->prepare("SELECT * FROM parking_tiers WHERE parking_id = ? ORDER BY day_of_week, open_time");
            $stmt->execute([$dto->parkingId]);
            $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les grilles tarifaires
            $stmt = $this->pdo->prepare("SELECT * FROM pricing_tiers WHERE parking_id = ? ORDER BY time");
            $stmt->execute([$dto->parkingId]);
            $pricingTiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'parking' => [
                    'id' => $parking['id'],
                    'name' => $parking['name'],
                    'address' => $parking['address'],
                    'city' => $parking['city'],
                    'lat' => (float)$parking['latitude'],
                    'lng' => (float)$parking['longitude'],
                    'totalSpaces' => (int)$parking['total_spaces'],
                    'hourlyRate' => (float)$parking['hourly_rate'],
                    'owner' => $parking['owner_first_name'] . ' ' . $parking['owner_last_name'],
                    'openingHours' => $tiers,
                    'pricingTiers' => $pricingTiers
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getParkingSubscriptions(GetParkingSubscriptionsDTO $dto): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM subscription_types WHERE parking_id = ? ORDER BY monthly_price
            ");
            $stmt->execute([$dto->parkingId]);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'subscriptions' => array_map(function($s) {
                    return [
                        'id' => $s['id'],
                        'name' => $s['name'],
                        'description' => $s['description'],
                        'monthlyPrice' => (float)$s['monthly_price'],
                        'durationMonths' => (int)$s['duration_months']
                    ];
                }, $subscriptions),
                'count' => count($subscriptions)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function searchParkings(SearchParkingsDTO $dto): array {
        try {
            $checkTime = $dto->timestamp ? new DateTime($dto->timestamp) : new DateTime();

            // Recherche par distance (formule Haversine simplifiée)
            $stmt = $this->pdo->prepare("
                SELECT p.*,
                    (6371 * ACOS(
                        COS(RADIANS(?)) * COS(RADIANS(latitude)) * 
                        COS(RADIANS(longitude) - RADIANS(?)) + 
                        SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                    )) AS distance
                FROM parkings p
                HAVING distance <= ?
                ORDER BY distance
                LIMIT 50
            ");
            $stmt->execute([$dto->latitude, $dto->longitude, $dto->latitude, $dto->radiusKm]);
            $parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($parkings as $parking) {
                // Calculer les places disponibles
                $stmtOccupied = $this->pdo->prepare("
                    SELECT COUNT(*) FROM parkings_sessions 
                    WHERE parking_id = ? AND exit_time IS NULL
                ");
                $stmtOccupied->execute([$parking['id']]);
                $occupied = (int)$stmtOccupied->fetchColumn();

                $available = max(0, (int)$parking['total_spaces'] - $occupied);

                if ($available > 0) {
                    $results[] = [
                        'id' => $parking['id'],
                        'name' => $parking['name'],
                        'address' => $parking['address'],
                        'city' => $parking['city'],
                        'lat' => (float)$parking['latitude'],
                        'lng' => (float)$parking['longitude'],
                        'distance' => round((float)$parking['distance'], 2),
                        'totalSpaces' => (int)$parking['total_spaces'],
                        'availableSpaces' => $available,
                        'price' => (float)$parking['hourly_rate']
                    ];
                }
            }

            return [
                'success' => true,
                'parkings' => $results,
                'count' => count($results),
                'searchCenter' => [
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude
                ],
                'radiusKm' => $dto->radiusKm
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
