--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5351
--INI--
pcov.directory=tests/end-to-end/regression/5351/src/
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/5351/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\Issue5351\GreeterTest::testGreets
"PHPUnit\TestFixture\Issue5351\DoesNotExist" is not a valid target for code coverage

%sGreeterTest.php:18

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.


Code Coverage Report:
  %s

 Summary:
  Classes:  0.00% (0/1)
  Methods:  0.00% (0/1)
  Lines:    0.00% (0/1)

