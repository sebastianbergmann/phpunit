--TEST--
phpunit ../../_files/EmptyTestCaseTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/EmptyTestCaseTest.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) Warning
No tests found in class "PHPUnit\TestFixture\EmptyTestCaseTest".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
