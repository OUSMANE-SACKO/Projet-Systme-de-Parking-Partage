<?php
    function loadEnv(string $path): void {
        if (!is_file($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                [$k,$v] = explode('=', $line, 2);
                $k = trim($k); $v = trim($v);
                if ($k !== '' && getenv($k) === false) {
                    putenv("$k=$v");
                    $_ENV[$k] = $v;
                }
            }
        }
    }

    loadEnv(__DIR__ . '/../../.env');

    // Autoloader simple (PSR-0 style sans namespace hiérarchique)
    spl_autoload_register(function(string $class) {
        // Si namespaces: transformer \ en /
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $short = ($pos = strrpos($relative, DIRECTORY_SEPARATOR)) !== false
            ? substr($relative, $pos + 1)
            : $relative;

        $paths = [
            __DIR__ . '/../Domain/Entities/' . $short . '.php',
            __DIR__ . '/../Application/UseCases/' . $short . '.php',
            __DIR__ . '/../Application/DTO/' . $short . '.php',
            __DIR__ . '/../Domain/Repositories/' . $short . '.php',
            __DIR__ . '/../Infrastructure/Repositories/' . $short . '.php',
            __DIR__ . '/../Infrastructure/Controller/' . $short . '.php',
            __DIR__ . '/../Infrastructure/Database/' . $short . '.php',
            __DIR__ . '/../Infrastructure/Database/Factories/' . $short . '.php',
            __DIR__ . '/../Infrastructure/Security/' . $short . '.php',
        ];

        foreach ($paths as $p) {
            if (is_file($p)) {
                require_once $p;
                return;
            }
        }
    });
?>