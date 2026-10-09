--TEST--
A property is shrunk when it is falsified by a failed assertion, an exception, an error, an unmet expectation for a test double, or a failure in tearDown()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FalsificationKindsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FEEFF                                                               5 / 5 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::testException
RuntimeException: value is too large

Property falsified after %s, counterexample shrunk in %s:

    $value = 101;

%sFalsificationKindsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::testError
TypeError: PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::expectsString(): Argument #1 ($value) must be of type string, int given, called in %sFalsificationKindsTest.php on line %d

Property falsified after %s, counterexample shrunk in %s:

    $value = 101;

%sFalsificationKindsTest.php:%d
%sFalsificationKindsTest.php:%d

--

There were 3 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::testFailedAssertion
Failed asserting that -1 is equal to 0 or is greater than 0.

Property falsified after %s, counterexample shrunk in %s:

    $values = [-1];

%sFalsificationKindsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::testUnmetExpectationForATestDouble
Countable::count() was expected to be invoked once but was never invoked.

Property falsified after %s, counterexample shrunk in %s:

    $value = 101;

3) PHPUnit\TestFixture\PropertyBasedTesting\FalsificationKindsTest::testFailureInTearDown
Failed asserting that 101 is equal to 100 or is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    $value = 101;

%sFalsificationKindsTest.php:%d

ERRORS!
Tests: 5, Assertions: %d, Errors: 2, Failures: 3.
