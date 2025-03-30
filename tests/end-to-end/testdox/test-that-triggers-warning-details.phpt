--TEST--
TestDox: Test triggers warning and --display-warning is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/WarningTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Warning (PHPUnit\TestFixture\TestDox\Warning)
 ⚠ Warning

1 test triggered 1 warning:

1) %sWarningTest.php:20
warning

OK, but there were issues!
Tests: 1, Assertions: 1, Warnings: 1.
