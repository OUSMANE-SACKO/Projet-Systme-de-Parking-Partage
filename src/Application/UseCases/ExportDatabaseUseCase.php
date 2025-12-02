<?php

require_once __DIR__ . '/../../Domain/Repositories/IUserRepository.php';
require_once __DIR__ . '/../../Domain/Repositories/IParkingRepository.php';


class ExportDatabaseUseCase {
    private IUserRepository $userRepository;
    private IParkingRepository $parkingRepository;
    private string $encryptionKey;

    public function __construct(
        IUserRepository $userRepository, 
        IParkingRepository $parkingRepository,
        string $encryptionKey 
    ) {
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->encryptionKey = $encryptionKey;
    }

    public function execute(): string {
        $users = $this->getAllUsersData();
        $parkings = $this->getAllParkingsData();
        
        $csvContent = "--- USERS ---\n";
        $csvContent .= $this->arrayToCsv($users);
        
        $csvContent .= "\n--- PARKINGS ---\n";
        $csvContent .= $this->arrayToCsv($parkings);

        return $this->encryptData($csvContent);
    }

    private function getAllUsersData(): array {
        $users = $this->userRepository->findAll(); 
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ];
        }
        return $data;    
    }

    private function getAllParkingsData(): array {
        $parkings = $this->parkingRepository->findAll();
        $data = [];
        foreach ($parkings as $parking) {
            $data[] = [
                'id' => $parking->getId(),
                'capacity' => $parking->getCapacity(),
                // 'owner_id' => ... (si disponible)
            ];
        }
        return $data;
    }

    private function arrayToCsv(array $data): string {
        if (empty($data)) {
            return "";
        }
        
        $output = fopen('php://temp', 'r+');
        fputcsv($output, array_keys($data[0]));

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    private function encryptData(string $data): string {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        $encrypted = openssl_encrypt($data, $cipher, $this->encryptionKey, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
}
?>