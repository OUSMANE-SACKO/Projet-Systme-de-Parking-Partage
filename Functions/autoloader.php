<?php
    // Parser .env manuel (sans Composer/dépendances)
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    // Charger .env
    loadEnv(__DIR__ . '/../.env');

    // Autoloader intelligent multi-dossiers
    spl_autoload_register(function (string $class) {
        // Extraire le nom court de la classe (sans namespace)
        $short = $class;
        if (false !== ($pos = strrpos($class, '\\'))) {
            $short = substr($class, $pos + 1);
        }
        
        // Répertoires à scanner (dans l'ordre de priorité)
        $directories = [
            // Structure actuelle (compatibilité)
            __DIR__ . '/../Entities/',
            __DIR__ . '/../UseCase/',
            __DIR__ . '/../Infrastructure/Database/',
            __DIR__ . '/../Infrastructure/Database/Factories/',
            __DIR__ . '/../Infrastructure/Repositories/',
            __DIR__ . '/../Domain/Repositories/',
            
            // Structure future (src/)
            __DIR__ . '/../src/Domain/Entities/',
            __DIR__ . '/../src/Domain/Repositories/',
            __DIR__ . '/../src/Application/UseCases/',
            __DIR__ . '/../src/Infrastructure/Database/',
            __DIR__ . '/../src/Infrastructure/Database/Factories/',
            __DIR__ . '/../src/Infrastructure/Repositories/',
        ];
        
        // Chercher le fichier dans tous les répertoires
        foreach ($directories as $dir) {
            $file = $dir . $short . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    });
?>