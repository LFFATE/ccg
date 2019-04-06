<?php

use terminal\Terminal;
use filesystem\Filesystem;

require_once(__DIR__ . '/app/helpers/helpers.php');
require_once(__DIR__ . '/config/config.php');

/**
 * autoload
 */
spl_autoload_register(function ($class) {
    include 'app/' . $class . '.php';
});

define('ROOT_DIR', realpath(__DIR__));

$defaults_normalized   = flat_array_with_prefix($defaults);

$config             = new Config($argv, $defaults_normalized);
$terminal           = new Terminal();
$filesystem         = new Filesystem();

$mediator = new Controller(
    $config,
    $terminal,
    $filesystem
);

$mediator
    ->generate();
    //->getOutput();
