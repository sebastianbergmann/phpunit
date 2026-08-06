--TEST--
The --disable-coverage-targeting CLI option also disables the strict enforcement of code coverage metadata
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--disable-coverage-targeting';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-runner/phpunit-strict-coverage.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/test-runner/.phpunit.cache.strict-coverage');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriver 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes: 100.00% (2/2)
  Methods: 100.00% (2/2)
  Lines:   100.00% (2/2)

PHPUnit\TestFixture\TestRunner\Covered
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
PHPUnit\TestFixture\TestRunner\NotCovered
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
