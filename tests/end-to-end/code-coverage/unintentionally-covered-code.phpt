--TEST--
A test that executes code that is not a code coverage target is considered risky when code coverage metadata is enforced strictly
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
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

There was 1 risky test:

1) PHPUnit\TestFixture\TestRunner\PassingTest::testOne
This test executed code that is not listed as code to be covered or used:
- PHPUnit\TestFixture\TestRunner\NotCovered

%s%etests%ePassingTest.php:18

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:  0.00% (0/2)
  Methods:  0.00% (0/2)
  Lines:    0.00% (0/2)
