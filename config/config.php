<?php

$defaults   = [];
$customs    = [];

require_once(__DIR__ . '/defaults.php');
require_once(__DIR__ . '/filesystem.php');

if (file_exists(__DIR__ . '/custom.php')) {
    include_once(__DIR__ . '/custom.php');
}

$defaults = array_merge($defaults, $customs);