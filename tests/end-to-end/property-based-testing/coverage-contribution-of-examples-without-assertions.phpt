--TEST--
A property contributes to code coverage when only its examples without assertions contribute to code coverage
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage-contribution-of-examples-without-assertions/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/coverage-contribution-of-examples-without-assertions/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriver 1.0.0
Configuration: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, %d assertions)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:%w100.00% (1/1)
  Methods:%w100.00% (1/1)
  Lines:%w100.00% (1/1)

PHPUnit\TestFixture\PropertyBasedTesting\CoverageContributionOfExamplesWithoutAssertions\Calculator
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
