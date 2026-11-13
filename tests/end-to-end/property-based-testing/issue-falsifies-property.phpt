--TEST--
An example that triggers an issue for which the test run fails falsifies the property, which is reported as a failure with the issue and the minimal counterexample
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--fail-on-warning';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueFalsifiesPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF.FWW                                                              6 / 6 (100%)

Time: %s, Memory: %s

There were 3 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueFalsifiesPropertyTest::testTriggersWarningForLargeValue
E_USER_WARNING was triggered at %sIssueFalsifiesPropertyTest.php:35: value is too large

Property falsified after %d examples, counterexample shrunk in %d steps:

    $value = 100;

2) PHPUnit\TestFixture\PropertyBasedTesting\IssueFalsifiesPropertyTest::testTriggersWarningForLargeValueWithoutPerformingAssertions
E_USER_WARNING was triggered at %sIssueFalsifiesPropertyTest.php:45: value is too large

Property falsified after %d examples, counterexample shrunk in %d steps:

    $value = 100;

3) PHPUnit\TestFixture\PropertyBasedTesting\IssueFalsifiesPropertyTest::testTriggersWarningForExplicitExample
E_USER_WARNING was triggered at %sIssueFalsifiesPropertyTest.php:64: value is 500

Property falsified by explicit example #0:

    $value = 500;

--

5 tests triggered 5 warnings:

1) %sIssueFalsifiesPropertyTest.php:35
value is too large

2) %sIssueFalsifiesPropertyTest.php:45
value is too large

3) %sIssueFalsifiesPropertyTest.php:64
value is 500

4) %sIssueFalsifiesPropertyTest.php:74
value is too large

5) %sIssueFalsifiesPropertyTest.php:85
example is skipped

FAILURES!
Tests: 6, Assertions: %d, Failures: 3, Warnings: 5, Skipped: 1.
