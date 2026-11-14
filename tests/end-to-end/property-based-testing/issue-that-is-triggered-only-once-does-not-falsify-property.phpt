--TEST--
An issue for which the test run fails, and that an example does not trigger again when it is replayed, does not falsify the property: the issue is reported once, and the property is only falsified in the way in which the replays or further examples falsify it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--fail-on-warning';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueTriggeredOnlyOnceTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

WFF                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredOnlyOnceTest::testTriggersWarningOnlyForTheFirstExampleAndFailsForLargeValue
Failed asserting that 100 is less than 100.

Property falsified after %d examples, counterexample shrunk in %d steps:

    $value = 100;

%sIssueTriggeredOnlyOnceTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredOnlyOnceTest::testTriggersWarningOnlyForTheFirstExampleAndAnotherWarningForLargeValue
E_USER_WARNING was triggered at %sIssueTriggeredOnlyOnceTest.php:76: value is too large

Property falsified after 1 example%s:

    $value = 100;

--

3 tests triggered 4 warnings:

1) %sIssueTriggeredOnlyOnceTest.php:48
triggered only once

2) %sIssueTriggeredOnlyOnceTest.php:60
triggered only once

3) %sIssueTriggeredOnlyOnceTest.php:72
triggered only once

4) %sIssueTriggeredOnlyOnceTest.php:76
value is too large

FAILURES!
Tests: 3, Assertions: %d, Failures: 2, Warnings: 4.
