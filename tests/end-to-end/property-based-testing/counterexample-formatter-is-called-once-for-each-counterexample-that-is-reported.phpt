--TEST--
The counterexample formatter is called once for each counterexample that is reported, with arguments of its own, the issues that it triggers are reported for the property, and the output that it prints is discarded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/CounterexampleFormatterCallsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FEFFFFF.                                                            8 / 8 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterIsCalledOnceForCounterexampleOfFlakyProperty
PHPUnit\Framework\PropertyBased\FlakyPropertyException: The property is flaky: it was not falsified when the example that falsified it was replayed 4 times, so its outcome does not depend on its arguments alone

Property falsified after 3 examples with this counterexample:

    $value = %d;

    value %d, call 2 of the formatter

The example that falsified the property did not falsify it again in 4 replays.

Caused by
Failed asserting that 3 is not identical to 3.

%sCounterexampleFormatterCallsTest.php:%d

--

There were 6 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterIsCalledOnceForMinimalCounterexample
Failed asserting that 100 is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    $value = 100;

    value 100, call 1 of the formatter

%sCounterexampleFormatterCallsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterIsCalledOnceForExplicitExampleThatFalsifiesProperty
Failed asserting that 500 is less than 100.

Property falsified by explicit example #1:

    $value = 500;

    value 500, call 3 of the formatter

%sCounterexampleFormatterCallsTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterIsCalledOnceForEachWayInWhichPropertyIsFalsified
Failed asserting that 50 is less than 50.

Property falsified after %s, counterexample shrunk in %s:

    $value = 50;

    value 50, call 4 of the formatter

Property also falsified by RuntimeException at %sCounterexampleFormatterCallsTest.php:%d with this counterexample:

    $value = %d;

    value %d, call 5 of the formatter

%sCounterexampleFormatterCallsTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterThatChangesItsArgument
Failed asserting that 3 is less than 3.

Property falsified after %s, counterexample shrunk in %s:

    $stack = ArrayObject Object #%d (
        0 => 0,
        1 => 0,
        2 => 0,
    );

    stack of 3

%sCounterexampleFormatterCallsTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterThatTriggersWarning
Failed asserting that 100 is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    $value = 100;

    value 100

%sCounterexampleFormatterCallsTest.php:%d

6) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterCallsTest::testFormatterThatPrintsOutput
Failed asserting that 100 is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    $value = 100;

    value 100

%sCounterexampleFormatterCallsTest.php:%d

--

1 test triggered 1 warning:

1) %sCounterexampleFormatterCallsTest.php:%d
triggered by formatter

ERRORS!
Tests: 8, Assertions: %d, Errors: 1, Failures: 6, Warnings: 1.
