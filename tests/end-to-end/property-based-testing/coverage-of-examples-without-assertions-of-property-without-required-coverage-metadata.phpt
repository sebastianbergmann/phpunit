--TEST--
The code coverage data of the examples of a property that do not perform assertions is discarded when the property does not define a code coverage target but is expected to do so
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/coverage-of-examples-without-assertions/require-coverage-metadata.xml';

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

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\PropertyWithoutCoverageMetadataTest::testExecutesCodeOnlyInExamplesWithoutAssertions
This test does not define a code coverage target but is expected to do so

%sPropertyWithoutCoverageMetadataTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: %d, Risky: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:%w0.00% (0/2)
  Methods:%w0.00% (0/2)
  Lines:%w0.00% (0/2)

PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\Calculator
  Methods:   0.00% ( 0/ 1)   Lines:   0.00% (  0/  1)
PHPUnit\TestFixture\PropertyBasedTesting\CoverageOfExamplesWithoutAssertions\Doubler
  Methods:   0.00% ( 0/ 1)   Lines:   0.00% (  0/  1)
