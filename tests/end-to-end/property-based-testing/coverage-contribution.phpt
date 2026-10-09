--TEST--
Whether a property contributes to code coverage is decided on all of its examples when coverage contribution is required
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage-contribution/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/coverage-contribution/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriver 1.0.0
Configuration: %s
Random Seed:   1234

.R                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\CoverageContribution\PropertyTest::testNoExampleContributesToCodeCoverage
This test does not contribute to code coverage

%sPropertyTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: %d, Risky: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:%w100.00% (1/1)
  Methods:%w100.00% (1/1)
  Lines:%w100.00% (1/1)

PHPUnit\TestFixture\PropertyBasedTesting\CoverageContribution\Calculator
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
