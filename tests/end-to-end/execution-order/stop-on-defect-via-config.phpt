--TEST--
phpunit -c ../_files/configuration_stop_on_defect.xml ./tests/_files/StopOnWarningTestSuite.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/configuration_stop_on_defect.xml');
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/StopOnWarningTestSuite.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

W

Time: %s, Memory: %s

There was 1 warning:

1) Warning
No tests found in class "PHPUnit\TestFixture\NoTestCases".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
