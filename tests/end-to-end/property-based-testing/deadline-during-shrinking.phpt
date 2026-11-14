--TEST--
Shrinking stops once the deadline of the test run has passed, which is stated together with the counterexample
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowShrinkingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

The time limit of 1 second for the test run was exceeded.

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\SlowShrinkingTest::testTakesLongerToShrinkThanTheTimeLimitOfTheTestRun
Failed asserting that %i is less than 1000.

Property falsified after 5 examples%s

    $value = %i;

Shrinking was stopped after %s because the test run was aborted, a simpler counterexample may exist.

%sSlowShrinkingTest.php:%d

FAILURES!
Tests: 1, Assertions: 5, Failures: 1.
