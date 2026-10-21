--TEST--
A property that is not falsified when the example that falsified it is replayed is reported as flaky, without shrinking its counterexample, together with the failure that was originally observed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FlakyPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\FlakyPropertyTest::testFailsOnlyForTheThirdExample
PHPUnit\Framework\PropertyBased\FlakyPropertyException: The property is flaky: it was not falsified when the example that falsified it was replayed 4 times, so its outcome does not depend on its arguments alone

Property falsified after 3 examples with this counterexample:

    $value = -7;

The example that falsified the property did not falsify it again in 4 replays.

Caused by
the third example fails
Failed asserting that 3 is not identical to 3.

%sFlakyPropertyTest.php:%d

ERRORS!
Tests: 1, Assertions: 3, Errors: 1.
