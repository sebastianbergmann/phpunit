--TEST--
Objects of a class are generated without an argument for a parameter of its constructor that has a default value when no generator can be derived for the type of that parameter
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/OptionalConstructorParametersTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\PropertyBasedTesting\OptionalConstructorParametersTest::testNullableParameterWithoutDefaultValue
Property PHPUnit\TestFixture\PropertyBasedTesting\OptionalConstructorParametersTest::testNullableParameterWithoutDefaultValue cannot be checked: cannot derive a generator for parameter $value of type PHPUnit\TestFixture\PropertyBasedTesting\NullableConstructorParameterWithoutDefaultValue: cannot derive a generator for parameter $previous of PHPUnit\TestFixture\PropertyBasedTesting\NullableConstructorParameterWithoutDefaultValue::__construct() of type ?Throwable: Throwable is an interface

%sOptionalConstructorParametersTest.php:%d

--

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\OptionalConstructorParametersTest::testValuesAreGeneratedForParameterWithDefaultValueForWhichGeneratorCanBeDerived
Failed asserting that 3 is less than 3.

Property falsified after %s:

    $notification = new Notification(message: '', priority: 3);

%sOptionalConstructorParametersTest.php:%d

ERRORS!
Tests: 2, Assertions: %d, Errors: 1, Failures: 1.
