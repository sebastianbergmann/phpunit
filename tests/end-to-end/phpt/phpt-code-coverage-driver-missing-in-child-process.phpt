--TEST--
A warning is emitted when code coverage cannot be collected in the child process of a PHPT test
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--INI--
xdebug.mode=coverage
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-code-coverage-driver-missing-in-child-process/src';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-code-coverage-driver-missing-in-child-process/test.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Code coverage could not be collected for %stest.phpt

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.


Code Coverage Report:
  %s

 Summary:
  Classes:  0.00% (0/1)
  Methods:  0.00% (0/1)
  Lines:    0.00% (0/1)
