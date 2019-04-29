<?php

spl_autoload_register(function ($class) {
    $file = realpath(__DIR__ . '/' . '..' . '/' . 'app') . '/' . str_replace('\\', '/', $class) . '.php';
    if(file_exists($file)) {
        require_once($file);
    }
});
