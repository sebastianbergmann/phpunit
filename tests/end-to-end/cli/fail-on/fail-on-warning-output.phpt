--TEST--
Details for warnings are displayed when --fail-on-warning is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--fail-on-warning';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/WarningTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

W.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

1 test triggered 1 warning:

1) %sWarningTest.php:%d
message

OK, but there were issues!
Tests: 2, Assertions: 2, Warnings: 1.
