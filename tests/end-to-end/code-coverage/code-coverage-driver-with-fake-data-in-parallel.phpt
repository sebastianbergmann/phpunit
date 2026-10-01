--TEST--
The code coverage data that is collected in a parallel worker is used for the code coverage report
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver/phpunit-with-fake-data-in-parallel.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver/.phpunit.cache.with-fake-data-in-parallel');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with CustomDriverWithFakeData 1.0.0
Configuration: %s
Parallel:      2 workers

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:  100.00% (1/1)
  Methods:  100.00% (1/1)
  Lines:    100.00% (1/1)

PHPUnit\TestFixture\CodeCoverageDriver\Foo
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
