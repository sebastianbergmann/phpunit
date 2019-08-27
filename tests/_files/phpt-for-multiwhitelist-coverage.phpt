--TEST--
PHPT for testing coverage using multiple whitespace arguments
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
$coveredClass = new CoveredClass();
$coveredClass->publicMethod();
$anotherCoveredClass = new SampleClass(1, 2, 'a');
$testing = $anotherCoveredClass->a;
--EXPECT--
