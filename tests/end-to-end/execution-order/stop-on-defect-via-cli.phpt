--TEST--
phpunit --stop-on-defect ./tests/_files/StopOnWarningTestSuite.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-defect';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/StopOnWarningTestSuite.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s #StandWithUkraine

W

Time: %s, Memory: %s

There was 1 warning:

1) Warning
No tests found in class "PHPUnit\TestFixture\NoTestCases".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
