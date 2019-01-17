--TEST--
PHPT for testing coverage
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
$coveredClass = new CoveredClass();
$coveredClass->publicMethod();
--EXPECT--
