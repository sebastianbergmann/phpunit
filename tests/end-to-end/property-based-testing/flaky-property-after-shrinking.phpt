--TEST--
A property whose example that falsified it falsifies it again when it is replayed, but whose minimal counterexample does not falsify it when it is replayed after shrinking, is reported as flaky, together with the failure that was originally observed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FlakyAfterShrinkingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\FlakyAfterShrinkingTest::testFailsForLargeValuesUntilItWasCalledTwelveTimes
PHPUnit\Framework\PropertyBased\FlakyPropertyException: The property is flaky: it was not falsified when its minimal counterexample was replayed, so its outcome does not depend on its arguments alone

Property falsified after %s with this counterexample:

    $value = %i;

Caused by
large values fail until the property was called twelve times
Failed asserting that %i is less than 100.

%sFlakyAfterShrinkingTest.php:%d

ERRORS!
Tests: 1, Assertions: %d, Errors: 1.
