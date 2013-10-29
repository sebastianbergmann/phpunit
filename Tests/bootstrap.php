<?php
require __DIR__ . '/../PHPUnit/Autoload.php';
require __DIR__ . '/../PHPUnit/Framework/Assert/Functions.php';
require __DIR__ . '/_files/CoveredFunction.php';
require __DIR__ . '/autoload.php';

if (!ini_get('date.timezone') && !function_exists('fb_enable_code_coverage')) {
  echo PHP_EOL . 'Error: PHPUnit\'s test suite requires the "date.timezone" runtime configuration to be set. Please check your php.ini.' . PHP_EOL;
  exit(1);
}

ini_set('precision', 14);
ini_set('serialize_precision', 14);
