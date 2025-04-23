--TEST--
phpunit --display-incomplete --display-skipped --display-deprecations --display-errors --display-notices --display-warnings ../_files/OutcomesAndIssuesTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-incomplete';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/../_files/OutcomesAndIssuesTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.RDNWFFFEEEDNWDNW                                                 17 / 17 (100%)

Time: %s, Memory: %s

There were 3 errors:

1) PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithDeprecation
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

2) PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithNotice
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

3) PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithWarning
Exception: exception message

%sOutcomesAndIssuesTest.php:%d

--

There were 3 failures:

1) PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithDeprecation
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

2) PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithNotice
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

3) PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithWarning
Failed asserting that false is true.

%sOutcomesAndIssuesTest.php:%d

--

There was 1 risky test:

1) PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithRisky
This test did not perform any assertions

%sOutcomesAndIssuesTest.php:%d

--

There were 3 incomplete tests:

1) PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithDeprecation
incomplete message

%sOutcomesAndIssuesTest.php:%d

2) PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithNotice
incomplete message

%sOutcomesAndIssuesTest.php:%d

3) PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithWarning
incomplete message

%sOutcomesAndIssuesTest.php:%d

--

There were 3 skipped tests:

1) PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithDeprecation
skipped message

2) PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithNotice
skipped message

3) PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithWarning
skipped message

--

5 tests triggered 5 warnings:

1) %sOutcomesAndIssuesTest.php:%d
warning message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithWarning
  %sOutcomesAndIssuesTest.php:%d

2) %sOutcomesAndIssuesTest.php:%d
warning message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithWarning
  %sOutcomesAndIssuesTest.php:%d

3) %sOutcomesAndIssuesTest.php:%d
warning message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithWarning
  %sOutcomesAndIssuesTest.php:%d

4) %sOutcomesAndIssuesTest.php:%d
warning message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithWarning
  %sOutcomesAndIssuesTest.php:%d

5) %sOutcomesAndIssuesTest.php:%d
warning message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithWarning
  %sOutcomesAndIssuesTest.php:%d

--

5 tests triggered 5 notices:

1) %sOutcomesAndIssuesTest.php:%d
notice message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithNotice
  %sOutcomesAndIssuesTest.php:%d

2) %sOutcomesAndIssuesTest.php:%d
notice message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithNotice
  %sOutcomesAndIssuesTest.php:%d

3) %sOutcomesAndIssuesTest.php:%d
notice message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithNotice
  %sOutcomesAndIssuesTest.php:%d

4) %sOutcomesAndIssuesTest.php:%d
notice message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithNotice
  %sOutcomesAndIssuesTest.php:%d

5) %sOutcomesAndIssuesTest.php:%d
notice message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithNotice
  %sOutcomesAndIssuesTest.php:%d

--

5 tests triggered 5 deprecations:

1) %sOutcomesAndIssuesTest.php:%d
deprecation message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithDeprecation
  %sOutcomesAndIssuesTest.php:%d

2) %sOutcomesAndIssuesTest.php:%d
deprecation message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithDeprecation
  %sOutcomesAndIssuesTest.php:%d

3) %sOutcomesAndIssuesTest.php:%d
deprecation message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithDeprecation
  %sOutcomesAndIssuesTest.php:%d

4) %sOutcomesAndIssuesTest.php:%d
deprecation message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithDeprecation
  %sOutcomesAndIssuesTest.php:%d

5) %sOutcomesAndIssuesTest.php:%d
deprecation message

Triggered by:

* PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithDeprecation
  %sOutcomesAndIssuesTest.php:%d

ERRORS!
Tests: 17, Assertions: 7, Errors: 3, Failures: 3, Warnings: 5, Deprecations: 5, Notices: 5, Skipped: 3, Incomplete: 3, Risky: 1.
