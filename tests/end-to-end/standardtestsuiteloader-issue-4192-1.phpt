--TEST--
phpunit ../../_files/ConcreteTest.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
$cmd = new \PHPUnit\TextUI\Command();
$cmd->run([
    'phpunit',
    (new \ReflectionClass(\ConcreteTest::class))->getFileName()
], false);
--EXPECTF--
PHPUnit %s [36m#StandWith[0m[33mUkraine[0m

Runtime:       PHP %s
Configuration: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
