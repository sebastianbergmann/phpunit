--TEST--
A custom code coverage driver that supports branch coverage but not path coverage can be used when only branch coverage is requested
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver/phpunit-with-branch-coverage.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver/.phpunit.cache.with-branch-coverage');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with CustomDriverWithBranchCoverage 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes: 100.00% (1/1)
  Methods: 100.00% (1/1)
  Branches:   100.00% (1/1)
  Lines:   100.00% (1/1)

PHPUnit\TestFixture\CodeCoverageDriver\Foo
  Methods: 100.00% ( 1/ 1)   Branches: 100.00% (  1/  1)   Lines: 100.00% (  1/  1)
