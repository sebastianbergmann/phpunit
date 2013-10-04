<?php
require __DIR__ . '/../PHPUnit/Autoload.php';
require __DIR__ . '/../PHPUnit/Framework/Assert/Functions.php';
require __DIR__ . '/_files/CoveredFunction.php';
require __DIR__ . '/autoload.php';

if (!ini_get('date.timezone')) {
  echo 'ERROR: These tests will not pass unless date.timzone is set in php.ini';
  exit(1);
}