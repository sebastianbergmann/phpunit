--TEST--
Unexpected output is printed as a record attributed to the test that printed it in the compact output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/PrintsUnexpectedOutputTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- OUTPUT: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testPassingTestThatPrintsOutput
output of passing test

--- FAILURE: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testFailingTestThatPrintsOutput
Failed asserting that false is true.

%sPrintsUnexpectedOutputTest.php:%d

--- OUTPUT: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testFailingTestThatPrintsOutput
output of failing test

--- OUTPUT: PHPUnit\TestFixture\PrintsUnexpectedOutputTest::testPrintsOnlyLineFeed

FAILURES (3 tests, 3 assertions, 1 failure)
