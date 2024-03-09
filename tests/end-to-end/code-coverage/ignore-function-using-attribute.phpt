--TEST--
Functions can be ignored for code coverage using an attribute
--INI--
pcov.directory=tests/end-to-end/code-coverage/ignore-function-using-attribute/src/
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/ignore-function-using-attribute/src';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = __DIR__ . '/ignore-function-using-attribute/tests';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

OK (1 test, 1 assertion)


Code Coverage Report:
  %s

 Summary:
  Classes:        (0/0)
  Methods:        (0/0)
  Lines:   100.00% (1/1)

