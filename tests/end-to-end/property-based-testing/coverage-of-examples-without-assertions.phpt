--TEST--
The code coverage data of the examples of a property that do not perform assertions is used when the property as a whole performed assertions, and discarded otherwise
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage-of-examples-without-assertions/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/coverage-of-examples-without-assertions/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriver 1.0.0
Configuration: %s
Random Seed:   1234

.R                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\PropertyTest::testPerformsNoAssertions
This test did not perform any assertions

%sPropertyTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: %d, Risky: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:%w50.00% (1/2)
  Methods:%w50.00% (1/2)
  Lines:%w50.00% (1/2)

PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\Calculator
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  1/  1)
PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\Doubler
  Methods:   0.00% ( 0/ 1)   Lines:   0.00% (  0/  1)
