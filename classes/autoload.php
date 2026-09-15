<?php

spl_autoload_register(function ($className) {
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    $filePath  = __DIR__ . DIRECTORY_SEPARATOR . $classPath;

    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }

    return false;
});
