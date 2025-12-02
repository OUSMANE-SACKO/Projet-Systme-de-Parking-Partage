<?php

use PhpParser\Node\Expr\AssignOp\Mod;

require_once __DIR__ . '/../../Functions/autoloader.php';

require_once __DIR__ . '/../../Domain/Repositories/IUserRepository.php';
require_once __DIR__ . '/../../Domain/Repositories/IParkingRepository.php';
// require_once __DIR__ . '/../../Domain/Repositories/IVehicleRepository.php';
// require_once __DIR__ . '/../../Domain/Repositories/ITicketRepository.php';
// require_once __DIR__ . '/../../Domain/Repositories/ITicketTypeRepository.php';
// require_once __DIR__ . '/../../Domain/Repositories/ITicketStatusRepository.php';
// require_once __DIR__ . '/../../Domain/Repositories/ITicketTypeRepository.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

$response = [
  'status' => 'error',
  'message' => 'Endpoint not found'
];
$httpCode = 404;

try {
  if ($method === 'GET') {
    switch ($action) {
      case 'users':
        // URL attendue: /api.php?action=users
        $userRepo = new MySQLUserRepository(DatabaseManager::getMySQL());
        $useCase = new GetUsersUseCase($userRepo);
        $result = $useCase->execute($userRepo->findById((int)$_GET['id']));
        if ($result) {
          $httpCode = 200;
          $response = [
            'status' => 'success',
            'data' => $result
          ];
        } else {
          $httpCode = 404;
          $response = [
            'status' => 'error',
            'message' => 'User not found'
          ];
        }
        break;

      case 'get_parking':
        // URL attendue: /api.php?action=get_parking&id=1
        $id = $_GET['id'] ?? null;
        if (!$id) {
          throw new Exception('ID is required');
        }
        $parkingRepo = new MySQLParkingRepository(DatabaseManager::getMySQL());
        $useCase = new GetParkingInformationUseCase($parkingRepo);

        // Note: execute() retourne un DTO ou un tableau
        // Si execute() retourne un objet Entité, il faudra peut-être le convertir en tableau ici
        $result = $useCase->execute($parkingRepo->findById((int)$id));

        if ($result) {
          $httpCode = 200;
          $response = [
            'status' => 'success',
            'data' => $result
          ];
        } else {
          $httpCode = 404;
          $response = [
            'status' => 'error',
            'message' => 'Parking not found'
          ];
        }
        break;

        case 'healthcheck':
          // URL attendue: /api.php?action=healthcheck
          $httpCode = 200;
          $response = [
            'status' => 'success',
            'message' => 'OK'
          ];
          break;

      default:
        $httpCode = 404;
        $response = [
          'status' => 'error',
          'message' => 'Endpoint not found'
        ];
        break;

    }
  }

}catch (Exception $e) {
  $response = [
    'status' => 'error',
    'message' => $e->getMessage()
  ];
  $httpCode = 500;
}

echo json_encode($response);
http_response_code($httpCode);