--TEST--
Unexpected output is not printed as a record in the compact output when it is reported as risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = __DIR__ . '/../_files/PrintsUnexpectedOutputTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- FAILURE: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testFailingTestThatPrintsOutput
Failed asserting that false is true.

%sPrintsUnexpectedOutputTest.php:%d

FAILURES (3 tests, 3 assertions, 1 failure, 3 risky)

--- RISKY: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testPassingTestThatPrintsOutput
Test code or tested code printed unexpected output: output of passing test

--- RISKY: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testFailingTestThatPrintsOutput
Test code or tested code printed unexpected output: output of failing test

--- RISKY: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testPrintsOnlyLineFeed
Test code or tested code printed unexpected output: 
