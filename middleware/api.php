<?php
/**
 * Point d'entrée API du middleware
 * Reçoit les DTOs du frontend, les valide et les transmet au backend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/analyse.php';
require_once __DIR__ . '/../backend/Functions/autoloader.php';

// Charger les DTOs de requête du backend
require_once __DIR__ . '/../backend/Application/DTO/AuthenticateUserDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/RegisterCustomerDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/RegisterOwnerDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/ReserveParkingDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/SubscribeToSubscriptionDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/AddParkingDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingInfoDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingReservationsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingSessionsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingAvailabilityDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingRevenueDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetParkingSubscriptionsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetUnauthorizedDriversDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/AddSubscriptionTypeDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/UpdateParkingPricingDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/SearchParkingsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetUserReservationsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetUserSessionsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetUserSubscriptionsDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/GetReservationInvoiceDTO.php';

// Charger les DTOs de réponse
require_once __DIR__ . '/../backend/Application/DTO/Response/AuthResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/UserResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/ParkingResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/ParkingSearchResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/ReservationResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/ReservationListResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/SubscriptionResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/InvoiceResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/DashboardStatsResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/ErrorResponseDTO.php';
require_once __DIR__ . '/../backend/Application/DTO/Response/SuccessResponseDTO.php';
require_once __DIR__ . '/../backend/Infrastructure/Database/Factories/MySQLFactory.php';

/**
 * Classe de gestion des requêtes API
 */
class ApiHandler {
    private DTOSecurityAnalyzer $analyzer;
    private PDO $pdo;

    public function __construct() {
        try {
            $this->pdo = MySQLFactory::getConnection();
            $this->analyzer = new DTOSecurityAnalyzer($this->pdo);
        } catch (Exception $e) {
            $this->sendError('Erreur de connexion à la base de données', 500);
            exit;
        }
    }

    /**
     * Traite la requête entrante
     */
    public function handle(): void {
        try {
            // Vérifier la méthode
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendError('Méthode non autorisée', 405);
                return;
            }

            // Récupérer et parser le JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError('JSON invalide', 400);
                return;
            }

            if (!isset($data['dtoType'])) {
                $this->sendError('Type de DTO manquant', 400);
                return;
            }

            // Créer le DTO approprié
            $dto = $this->createDTO($data['dtoType'], $data);
            
            if (!$dto) {
                $this->sendError('Type de DTO inconnu: ' . $data['dtoType'], 400);
                return;
            }

            // Valider le DTO
            try {
                $dto->validate();
            } catch (InvalidArgumentException $e) {
                $this->sendError($e->getMessage(), 422);
                return;
            }

            // Passer par l'analyseur de sécurité et le routeur
            $result = $this->analyzer->handle($dto);
            
            $this->sendSuccess($result);

        } catch (InvalidArgumentException $e) {
            // Entrée bloquée par l'analyseur de sécurité
            $this->sendError('Entrée invalide détectée', 400);
        } catch (RuntimeException $e) {
            $this->sendError($e->getMessage(), 500);
        } catch (Exception $e) {
            error_log('API Error: ' . $e->getMessage());
            $this->sendError('Erreur interne du serveur', 500);
        }
    }

    /**
     * Crée le DTO approprié selon le type
     */
    private function createDTO(string $type, array $data): ?object {
        switch ($type) {
            // Authentification & Inscription
            case 'AuthenticateUserDTO':
                return AuthenticateUserDTO::fromArray($data);

            case 'RegisterCustomerDTO':
                return RegisterCustomerDTO::fromArray($data);

            case 'RegisterOwnerDTO':
                return RegisterOwnerDTO::fromArray($data);

            // Gestion des parkings
            case 'AddParkingDTO':
                return AddParkingDTO::fromArray($data);

            case 'GetParkingsDTO':
                return GetParkingsDTO::fromArray($data);

            case 'GetParkingInfoDTO':
                return GetParkingInfoDTO::fromArray($data);

            case 'SearchParkingsDTO':
                return SearchParkingsDTO::fromArray($data);

            case 'UpdateParkingPricingDTO':
                return UpdateParkingPricingDTO::fromArray($data);

            case 'GetParkingSubscriptionsDTO':
                return GetParkingSubscriptionsDTO::fromArray($data);

            case 'AddSubscriptionTypeDTO':
                return AddSubscriptionTypeDTO::fromArray($data);

            // Réservations
            case 'ReserveParkingDTO':
                return ReserveParkingDTO::fromArray($data);

            case 'GetParkingReservationsDTO':
                return GetParkingReservationsDTO::fromArray($data);

            case 'GetUserReservationsDTO':
                return GetUserReservationsDTO::fromArray($data);

            case 'GetReservationInvoiceDTO':
                return GetReservationInvoiceDTO::fromArray($data);

            // Stationnements (Sessions)
            case 'EnterExitParkingDTO':
                return EnterExitParkingDTO::fromArray($data);

            case 'GetParkingSessionsDTO':
                return GetParkingSessionsDTO::fromArray($data);

            case 'GetUserSessionsDTO':
                return GetUserSessionsDTO::fromArray($data);

            // Abonnements
            case 'SubscribeToSubscriptionDTO':
                return SubscribeToSubscriptionDTO::fromArray($data);

            case 'GetUserSubscriptionsDTO':
                return GetUserSubscriptionsDTO::fromArray($data);

            // Analytics propriétaire
            case 'GetParkingAvailabilityDTO':
                return GetParkingAvailabilityDTO::fromArray($data);

            case 'GetParkingRevenueDTO':
                return GetParkingRevenueDTO::fromArray($data);

            case 'GetUnauthorizedDriversDTO':
                return GetUnauthorizedDriversDTO::fromArray($data);

            default:
                return null;
        }
    }

    /**
     * Envoie une réponse de succès
     */
    private function sendSuccess($data): void {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Envoie une réponse d'erreur
     */
    private function sendError(string $message, int $code = 400): void {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code
        ]);
    }
}

// Exécuter le handler
$handler = new ApiHandler();
$handler->handle();
