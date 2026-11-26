--TEST--
phpunit --no-configuration ../../_files/SeparateProcessesTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/SeparateProcessesTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\SeparateProcessesTest::testFoo
Test was run in child process and ended unexpectedly

2) PHPUnit\TestFixture\SeparateProcessesTest::testBar
Test was run in child process and ended unexpectedly

ERRORS!
Tests: 2, Assertions: 0, Errors: 2.
