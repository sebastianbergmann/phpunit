--TEST--
phpunit --compact --display-all-issues ../_files/OutcomesAndIssuesTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--display-all-issues';
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

--- DEPRECATION: %sOutcomesAndIssuesTest.php:%d
deprecation message

--- DEPRECATION: %sOutcomesAndIssuesTest.php:%d
deprecation message

--- DEPRECATION: %sOutcomesAndIssuesTest.php:%d
deprecation message

--- DEPRECATION: %sOutcomesAndIssuesTest.php:%d
deprecation message

--- DEPRECATION: %sOutcomesAndIssuesTest.php:%d
deprecation message

--- WARNING: %sOutcomesAndIssuesTest.php:%d
warning message

--- WARNING: %sOutcomesAndIssuesTest.php:%d
warning message

--- WARNING: %sOutcomesAndIssuesTest.php:%d
warning message

--- WARNING: %sOutcomesAndIssuesTest.php:%d
warning message

--- WARNING: %sOutcomesAndIssuesTest.php:%d
warning message

--- NOTICE: %sOutcomesAndIssuesTest.php:%d
notice message

--- NOTICE: %sOutcomesAndIssuesTest.php:%d
notice message

--- NOTICE: %sOutcomesAndIssuesTest.php:%d
notice message

--- NOTICE: %sOutcomesAndIssuesTest.php:%d
notice message

--- NOTICE: %sOutcomesAndIssuesTest.php:%d
notice message

--- RISKY: PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithRisky
This test did not perform any assertions

--- INCOMPLETE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithDeprecation
incomplete message

--- INCOMPLETE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithNotice
incomplete message

--- INCOMPLETE: PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithWarning
incomplete message

--- SKIPPED: PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithDeprecation
skipped message

--- SKIPPED: PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithNotice
skipped message

--- SKIPPED: PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithWarning
skipped message
