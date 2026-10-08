--TEST--
A property that is not falsified when its minimal counterexample is replayed is reported as flaky, together with the failure that was originally observed
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
PHPUnit\Framework\PropertyBased\FlakyPropertyException: The property is flaky: it was not falsified when its minimal counterexample was replayed, so its outcome does not depend on its arguments alone

Property falsified after 3 examples with this counterexample:

    $value = -7;

Caused by
the third example fails
Failed asserting that 3 is not identical to 3.

%sFlakyPropertyTest.php:%d

ERRORS!
Tests: 1, Assertions: 3, Errors: 1.
