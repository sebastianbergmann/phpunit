--TEST--
phpunit ../../_files/ConcreteTest.php
--FILE--
<?php declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';
$cmd = new \PHPUnit\TextUI\Command();
$cmd->run([
    'phpunit',
    (new \ReflectionClass(\ConcreteTest::class))->getFileName()
], false);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Configuration: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
