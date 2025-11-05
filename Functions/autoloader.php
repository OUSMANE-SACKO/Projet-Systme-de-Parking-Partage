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

    // Autoloader pour vos entités
    spl_autoload_register(function (string $class) {
        $short = $class;
        if (false !== ($pos = strrpos($class, '\\'))) {
            $short = substr($class, $pos + 1);
        }

        $file = __DIR__ . '/../Entities/' . $short . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
?>