--TEST--
phpunit --parallel=2 runs PHPT tests one at a time when code coverage is active so they do not collide on the shared coverage
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/skip-if-requires-code-coverage-driver.php';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/_files/src';
$_SERVER['argv'][] = __DIR__ . '/_files/greeter.phpt';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:
  %s

 Summary:
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Lines:   100.00% (1/1)

PHPUnit\TestFixture\ParallelPhptCoverage\Greeter
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
