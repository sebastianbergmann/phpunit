--TEST--
phpunit ../../_files/AbstractTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'AbstractTest';
$_SERVER['argv'][3] = __DIR__ . '/../_files/AbstractTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Warning: Calling PHPUnit with a class name is deprecated and will be removed in PHPUnit 9.
PHPUnit %s by Sebastian Bergmann and contributors.

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) Warning
Cannot instantiate class "AbstractTest".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
