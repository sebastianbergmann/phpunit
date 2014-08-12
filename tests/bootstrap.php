<?php
require __DIR__ . '/../vendor/autoload.php';

if (!ini_get('date.timezone') && !defined('HHVM_VERSION')) {
    echo PHP_EOL . 'Error: PHPUnit\'s test suite requires the "date.timezone" runtime configuration to be set. Please check your php.ini.' . PHP_EOL;
    exit(1);
}

ini_set('precision', 14);
ini_set('serialize_precision', 14);
