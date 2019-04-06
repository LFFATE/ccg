<?php

spl_autoload_register(function ($class) {
    $file = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app') . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if(file_exists($file)) {
        require_once($file);
    }
});
