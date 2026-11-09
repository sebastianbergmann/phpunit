--TEST--
A test whose covered code is executed more than once contributes to code coverage when coverage contribution is required and the code coverage driver collects hit counts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-runner/phpunit-require-coverage-contribution-with-hit-counts.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/test-runner/.phpunit.cache.require-coverage-contribution-with-hit-counts');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriverWithHitCounts 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:  100.00% (1/1)
  Methods:  100.00% (1/1)
  Lines:    100.00% (1/1)

PHPUnit\TestFixture\TestRunner\Covered
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
