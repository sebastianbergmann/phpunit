--TEST--
Test runner emits warning when --log-external is used with an invalid target path
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-external';
$_SERVER['argv'][] = '\PHPUnit\TestFixture\ExternalLoggerInvalid';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot log test results to external logger: "\PHPUnit\TestFixture\ExternalLoggerInvalid" does not implement PHPUnit\Logging\ExternalLogger

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
