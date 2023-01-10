--TEST--
phpunit ../../../_files/abstract/with-test-suffix/AbstractTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = 'AbstractTest';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/abstract/with-test-suffix/AbstractTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Warning:       Invocation with class name is deprecated

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) Warning
Cannot instantiate class "PHPUnit\TestFixture\AbstractTest".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
