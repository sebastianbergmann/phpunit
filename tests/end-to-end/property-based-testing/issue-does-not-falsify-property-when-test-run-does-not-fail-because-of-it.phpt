--TEST--
An example that triggers an issue for which the test run does not fail does not falsify the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueFalsifiesPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

WW.WWW                                                              6 / 6 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueFalsifiesPropertyTest::testTriggersWarningForLargeValueWithoutPerformingAssertions
This test did not perform any assertions

%sIssueFalsifiesPropertyTest.php:42

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

OK, but there were issues!
Tests: 6, Assertions: %d, Warnings: 5, Skipped: 1, Risky: 1.
