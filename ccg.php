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

$ccg = new Ccg(
    $config,
    $terminal,
    $filesystem
);

if ($config->get('debug')) {
    $ccg
        ->generate();
} else {
    try {
        $ccg
            ->generate();
    } catch (\Exception $error) {
        $terminal->error($error->getMessage());
    }
}
