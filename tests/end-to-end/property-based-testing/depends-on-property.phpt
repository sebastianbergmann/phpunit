--TEST--
A test that depends on a property is run when the property holds and skipped when it does not
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.F.S                                                                4 / 4 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\DependsOnPropertyTest::testDoesNotHold
Failed asserting that 0 is of type string.

Property falsified after 1 example, counterexample shrunk in 1 step:

    $value = 0;

%sDependsOnPropertyTest.php:%d

--

There was 1 skipped test:

1) PHPUnit\TestFixture\PropertyBasedTesting\DependsOnPropertyTest::testDependsOnPropertyThatDoesNotHold
This test depends on "PHPUnit\TestFixture\PropertyBasedTesting\DependsOnPropertyTest::testDoesNotHold" to pass

FAILURES!
Tests: 4, Assertions: 12, Failures: 1, Skipped: 1.
