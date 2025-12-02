<?php

require_once __DIR__ . '/src/Functions/autoloader.php';

  function decryptData(string $encryptedData, string $key): string {
    $cipher = "aes-256-cbc";
    $data = base64_decode($encryptedData);

    if ($data === false) {
      return "Erreur: Données invalides (non base64).";
    }
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $rawEncrypted = substr($data, $ivlen);

    $decrypted = openssl_decrypt($rawEncrypted, $cipher, $key, 0, $iv);
    if ($decrypted === false) {
      return "Erreur: Échec du déchiffrement (mauvaise clé ou fichier corrompu).";
    }

    return $decrypted !== false ? $decrypted : "Erreur: Échec du déchiffrement (mauvaise clé ou fichier corrompu).";
}
try {
    $inputFile = 'backup_db_encrypted.csv.enc';
    $outputFile = 'backup_db_decrypted.csv';

    if (!file_exists($inputFile)) {
        throw new Exception("Fichier non trouvé: $inputFile");
    }
    $encryptedContent = file_get_contents($inputFile);

    $secretKey = 'MaSuperCleSecrete1234567890123456';
    if (empty($secretKey)) {
      throw new Exception("Clé de déchiffrement non définie.");
    }

    echo "*... Déchiffrement de {$inputFile} en cours ...*\n";

    $decryptedContent = decryptData($encryptedContent, $secretKey);

    if (str_starts_with($decryptedContent, 'Erreur:')){
      throw new Exception($decryptedContent);
    }
    echo "*... Déchiffrement terminé ...*\n";

    echo "Contenu déchiffré:->";
    echo "{$decryptedContent}\n *... FIN DU CONTENU ...*\n";
    file_put_contents($outputFile, $decryptedContent);
    echo "*... Contenu déchiffré sauvegardé dans {$outputFile} ...*\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    echo "xXx Fin du programme. xXx\n";
}
?>