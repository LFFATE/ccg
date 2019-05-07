#!/usr/bin/php
<?php

use terminal\Terminal;
use filesystem\Filesystem;
use autocomplete\Autocomplete;

require __DIR__ . '/app/helpers/helpers.php';
require __DIR__ . '/config/config.php';

/**
 * autoload
 */
spl_autoload_register(function ($class) {
    $file = realpath(__DIR__ . '/' . 'app') . '/' . str_replace('\\', '/', $class) . '.php';
    if(file_exists($file)) {
        require_once($file);
    }
});

define('ROOT_DIR', sanitize_filename(__DIR__));

$defaults_normalized = flat_array_with_prefix($defaults);

$terminal           = new Terminal();
$config             = new Config($terminal->getArguments(), $defaults_normalized);
$filesystem         = new Filesystem();

$autocomplete   = new Autocomplete($config, $terminal, $filesystem);
$ccg            = new Ccg($config, $terminal, $filesystem, $autocomplete);

if ($terminal->isAutocomplete()) {
    $autocompletes = $ccg->autocomplete($terminal->getArguments());
    $terminal
        ->autocomplete($autocompletes)
        ->exit();
}

if ($config->get('debug')) {
    $ccg->generate();
} else {
    try {
        $ccg->generate();
    } catch (\Throwable $error) {
        $terminal->error($error->getMessage());
    }
}
