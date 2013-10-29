<?php
require __DIR__ . '/../PHPUnit/Autoload.php';
require __DIR__ . '/../PHPUnit/Framework/Assert/Functions.php';
require __DIR__ . '/_files/CoveredFunction.php';
require __DIR__ . '/autoload.php';

if (!ini_get('date.timezone')) {
  echo PHP_EOL . 'Error: To properly execute PHPUnits test suite you have to set "date.timezone". Please check your php.ini.' . PHP_EOL;
  exit(1);
}

