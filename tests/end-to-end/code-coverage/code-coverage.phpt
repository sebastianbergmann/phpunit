--TEST--
Add tests for code coverage attributes for class, method, function
--INI--
pcov.directory=tests/end-to-end/code-coverage/code-coverage
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/code-coverage/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s MB

OK (3 tests, 3 assertions)


Code Coverage Report:
  %s

 Summary:
  Classes: 50.00% (1/2)
  Methods: 50.00% (2/4)
  Lines:   50.00% (3/6)

PHPUnit\TestFixture\CodeCoverage\FullyTestedClass
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
PHPUnit\TestFixture\CodeCoverage\Method
  Methods:  33.33% ( 1/ 3)   Lines:  33.33% (  1/  3)
