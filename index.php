<?php
    spl_autoload_register(function (string $className) {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'Entities' . DIRECTORY_SEPARATOR
            . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
?>