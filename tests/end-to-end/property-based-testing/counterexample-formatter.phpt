--TEST--
The string that the method that is specified with #[CounterexampleFormatter] or #[CounterexampleFormatterExternal] returns for the arguments of the counterexample of a property is reported below these arguments
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/CounterexampleFormatterTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FFFFFF.                                                             7 / 7 (100%)

Time: %s, Memory: %s

There were 6 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterOfTestClass
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

    0 + 11
    is greater than 10

%sCounterexampleFormatterTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterOfAnotherClass
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

    0 * 11

%sCounterexampleFormatterTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterForExplicitExample
Failed asserting that 50 is equal to 10 or is less than 10.

Property falsified by explicit example #0:

    $a = 20;
    $b = 30;

    20 + 30
    is greater than 10

%sCounterexampleFormatterTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterThatRaisesAnException
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

    Counterexample formatter PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::raises() raised RuntimeException: message

%sCounterexampleFormatterTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterThatDoesNotReturnAString
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

    Counterexample formatter PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::returnsAnInteger() returned int instead of a string

%sCounterexampleFormatterTest.php:%d

6) PHPUnit\TestFixture\PropertyBasedTesting\CounterexampleFormatterTest::testFormatterThatReturnsAnEmptyString
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

%sCounterexampleFormatterTest.php:%d

FAILURES!
Tests: 7, Assertions: %d, Failures: 6.
