--TEST--
Different outcomes and issues (without TestDox summary)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--display-incomplete';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/OutcomeAndIssuesTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Outcome And Issues (PHPUnit\TestFixture\TestDox\OutcomeAndIssues)
 ✔ Success
 ⚠ Success but risky
 ⚠ Success but deprecation
 ⚠ Success but notice
 ⚠ Success but warning
 ✘ Failure
   │
   │ Failed asserting that false is true.
   │
   │ %sOutcomeAndIssuesTest.php:53
   │
 ✘ Error
   │
   │ Exception: message
   │
   │ %sOutcomeAndIssuesTest.php:58
   │
 ∅ Incomplete
   │
   │ message
   │
   │ %sOutcomeAndIssuesTest.php:63
   │
 ↩ Skipped

There was 1 risky test:

1) PHPUnit\TestFixture\TestDox\OutcomeAndIssuesTest::testSuccessButRisky
This test did not perform any assertions

%sOutcomeAndIssuesTest.php:26

--

1 test triggered 1 warning:

1) %sOutcomeAndIssuesTest.php:48
message

Triggered by:

* PHPUnit\TestFixture\TestDox\OutcomeAndIssuesTest::testSuccessButWarning
  %sOutcomeAndIssuesTest.php:44

--

1 test triggered 1 notice:

1) %sOutcomeAndIssuesTest.php:41
message

Triggered by:

* PHPUnit\TestFixture\TestDox\OutcomeAndIssuesTest::testSuccessButNotice
  %sOutcomeAndIssuesTest.php:37

--

1 test triggered 1 deprecation:

1) %sOutcomeAndIssuesTest.php:34
message

Triggered by:

* PHPUnit\TestFixture\TestDox\OutcomeAndIssuesTest::testSuccessButDeprecation
  %sOutcomeAndIssuesTest.php:30

ERRORS!
Tests: 9, Assertions: 5, Errors: 1, Failures: 1, Warnings: 1, Deprecations: 1, Notices: 1, Skipped: 1, Incomplete: 1, Risky: 1.
