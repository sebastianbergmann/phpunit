--TEST--
phpunit --compact ../_files/OutcomesAndIssuesTest (default display flags)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/OutcomesAndIssuesTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

ERRORS (17 tests, 7 assertions, 3 errors, 3 failures, 5 deprecations, 5 warnings, 5 notices, 3 skipped, 3 incomplete, 1 risky)

--- ERROR: PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithDeprecation
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

--- ERROR: PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithNotice
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

--- ERROR: PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithWarning
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

--- FAILURE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithDeprecation
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

--- FAILURE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithNotice
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

--- FAILURE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithWarning
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

--- RISKY: PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithRisky
This test did not perform any assertions
