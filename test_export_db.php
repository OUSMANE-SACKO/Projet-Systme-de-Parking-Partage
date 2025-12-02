<?php
require_once __DIR__ . '/src/Functions/autoloader.php';
require_once __DIR__ . '/src/Infrastructure/Database/SetupDatabase.php';

// Inclusions manuelles nécessaires hors autoloader standard
require_once __DIR__ . '/src/Domain/Entities/User.php';
require_once __DIR__ . '/src/Domain/Entities/Owner.php';
require_once __DIR__ . '/src/Domain/Entities/Customer.php';
require_once __DIR__ . '/src/Domain/Entities/Parking.php';
require_once __DIR__ . '/src/Domain/Repositories/IUserRepository.php';
require_once __DIR__ . '/src/Domain/Repositories/IParkingRepository.php';
require_once __DIR__ . '/src/Infrastructure/Repositories/MySQLUserRepository.php';
require_once __DIR__ . '/src/Infrastructure/Repositories/MySQLParkingRepository.php';
require_once __DIR__ . '/src/Application/UseCases/ExportDatabaseUseCase.php';

try {
    echo "\n--- TEST EXPORT DATABASE ---\n";

    // 1. Préparation
    $userRepo = new MySQLUserRepository();
    $parkingRepo = new MySQLParkingRepository();
    $secretKey = "MaSuperCleSecrete1234567890123456"; // Doit faire 32 caractères pour AES-256

    // 2. Exécution du Use Case
    echo "Génération de l'export chiffré...\n";
    $useCase = new ExportDatabaseUseCase($userRepo, $parkingRepo, $secretKey);
    $encryptedData = $useCase->execute();

    echo "✅ Données chiffrées reçues (" . strlen($encryptedData) . " octets)\n";
    echo "Aperçu : " . substr($encryptedData, 0, 50) . "...\n";

    // 3. Test de Déchiffrement (Pour vérifier que c'est valide)
    echo "\nTentative de déchiffrement...\n";
    
    // Fonction de déchiffrement locale pour le test
    $cipher = "aes-256-cbc";
    $data = base64_decode($encryptedData);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $rawEncrypted = substr($data, $ivlen);
    $decrypted = openssl_decrypt($rawEncrypted, $cipher, $secretKey, 0, $iv);

    if ($decrypted === false) {
        throw new Exception("Échec du déchiffrement !");
    }

    echo "✅ Déchiffrement réussi !\n";
    echo "Contenu du CSV :\n";
    echo "--------------------------------------------------\n";
    echo substr($decrypted, 0, 500); // Affiche les 500 premiers caractères
    echo "\n--------------------------------------------------\n";

    // 4. Sauvegarde dans un fichier (Optionnel)
    file_put_contents('backup_db_encrypted.csv.enc', $encryptedData);
    echo "Fichier 'backup_db_encrypted.csv.enc' sauvegardé.\n";

} catch (Exception $e) {
    echo "\n❌ ERREUR : " . $e->getMessage() . "\n";
}
?>
