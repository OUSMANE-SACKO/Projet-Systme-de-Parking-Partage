<?php
// filepath: c:\Users\marti\Documents\GitHub\Projet-Systme-de-Parking-Partage\middleware\analyse.php

class DTOSecurityAnalyzer {
    // Minimal patterns: SQL keywords/comments, simple logical injection, basic XSS hooks
    private const SQL_PATTERNS = [
        '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC)\b/i',
        '/(--|\/\*|\*\/|#|\bOR\b.*=|\bAND\b.*=)/i',
    ];
    private const XSS_PATTERNS = [
        '/<script\b/i',
        '/on\w+\s*=\s*/i',
        '/javascript:/i',
    ];

    // DTO → Controller file path
    private const DTO_ROUTES = [
        'RegisterCustomerDTO' => 'backend/Infrastructure/Controller/CustomerController.php',
        'RegisterOwnerDTO' => 'backend/Infrastructure/Controller/OwnerController.php',
        'AuthenticateUserDTO' => 'backend/Infrastructure/Controller/AuthController.php',
        'AddParkingDTO' => 'backend/Infrastructure/Controller/ParkingController.php',
        'ReserveParkingDTO' => 'backend/Infrastructure/Controller/ReservationController.php',
        'EnterExitParkingDTO' => 'backend/Infrastructure/Controller/ParkingSpaceController.php',
        'SubscribeToSubscriptionDTO' => 'backend/Infrastructure/Controller/SubscriptionController.php',
    ];

    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    private function isMalicious(string $value): bool {
        foreach (self::SQL_PATTERNS as $p) if (preg_match($p, $value)) return true;
        foreach (self::XSS_PATTERNS as $p) if (preg_match($p, $value)) return true;
        return false;
    }

    public function sanitize(mixed $value): mixed {
        if (!is_string($value)) return $value;
        if ($this->isMalicious($value)) {
            $this->log('InputBlocked', $value);
            throw new InvalidArgumentException('Invalid input');
        }
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    public function sanitizeDTO(object $dto): object {
        foreach (get_object_vars($dto) as $k => $v) {
            $dto->$k = is_array($v) ? array_map([$this, 'sanitize'], $v) : $this->sanitize($v);
        }
        return $dto;
    }

    // Single entry point: sanitize → resolve route → load controller → call action
    public function handle(object $dto): mixed {
        $dto = $this->sanitizeDTO($dto);
        [$file, $class, $action] = $this->resolveRoute($dto);

        $full = __DIR__ . '/../' . $file;
        if (!is_file($full)) throw new RuntimeException("Controller not found: $file");
        require_once $full;

        if (!class_exists($class)) throw new RuntimeException("Controller class not found: $class");
        $controller = new $class($this->pdo);
        if (!method_exists($controller, $action)) throw new RuntimeException("Action not found: $action");
        return $controller->$action($dto);
    }

    private function resolveRoute(object $dto): array {
        $dtoClass = get_class($dto);
        $file = self::DTO_ROUTES[$dtoClass] ?? null;
        if (!$file) throw new RuntimeException("Unknown DTO: $dtoClass");
        $class = $this->controllerClassFromPath($file);
        $action = $this->actionFromDTO($dtoClass);
        return [$file, $class, $action];
    }

    private function actionFromDTO(string $dtoClass): string {
        // e.g., RegisterCustomerDTO → registerCustomer
        $base = preg_replace('/DTO$/', '', $dtoClass);
        return lcfirst($base);
    }

    private function controllerClassFromPath(string $path): string {
        return pathinfo($path, PATHINFO_FILENAME); // e.g., CustomerController
    }

    private function log(string $type, string $value): void {
        $msg = sprintf(
            "[%s] %s from %s: %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            substr($value, 0, 120)
        );
        @file_put_contents(__DIR__ . '/../logs/security.log', $msg, FILE_APPEND);
    }
}
?>