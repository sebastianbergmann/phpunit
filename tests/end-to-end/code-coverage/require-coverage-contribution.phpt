--TEST--
A test that does not contribute to code coverage is considered risky when coverage contribution is required
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-runner/phpunit-require-coverage-contribution.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/test-runner/.phpunit.cache.require-coverage-contribution');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriverWithoutContribution 1.0.0
Configuration: %s

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\TestRunner\PassingTest::testOne
This test does not contribute to code coverage

%s%etests%ePassingTest.php:18

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:  0.00% (0/1)
  Methods:  0.00% (0/1)
  Lines:    0.00% (0/1)
