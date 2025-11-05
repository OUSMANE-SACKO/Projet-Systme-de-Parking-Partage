<?php
    require_once __DIR__ . '/vendor/autoload.php';

    if (class_exists(\Dotenv\Dotenv::class)) {
        \Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
    }
    
    spl_autoload_register(function (string $class) {
        $short = $class;
        if (false !== ($pos = strrpos($class, '\\'))) {
            $short = substr($class, $pos + 1);
        }

        // Mapper vers Entities/<NomDeClasse>.php
        $file = __DIR__ . '/Entities/' . $short . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
?>