--TEST--
PHPT for testing coverage
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
$coveredClass = new CoveredClass();
$coveredClass->publicMethod();
--EXPECT--
