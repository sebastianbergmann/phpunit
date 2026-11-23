--TEST--
phpunit reports the same code coverage without --parallel as it reports with --parallel=2 for the same tests
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/_files/src';
$_SERVER['argv'][] = __DIR__ . '/_files/tests';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)


Code Coverage Report:
  %s

 Summary:
  Classes:   66.66% (2/3)
  Methods:   75.00% (3/4)
  Lines:     75.00% (3/4)

PHPUnit\TestFixture\ParallelCodeCoverage\Alpha\One
  Methods: 100.00% ( 2/ 2)   Lines: 100.00% (  2/  2)
PHPUnit\TestFixture\ParallelCodeCoverage\Beta\Two
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
