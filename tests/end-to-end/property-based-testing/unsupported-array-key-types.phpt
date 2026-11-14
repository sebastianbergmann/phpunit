--TEST--
A property with a parameter of an array type whose keys are not integers or strings is reported as an error when the test suite is loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/UnsupportedArrayKeyTypesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 6 PHPUnit errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testBooleanKeys
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testBooleanKeys cannot be checked: cannot derive a generator for parameter $value of type array<bool, int>: array<bool, int> is not supported, the keys of an array are integers or strings, not bool

%sUnsupportedArrayKeyTypesTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testFloatingPointKeys
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testFloatingPointKeys cannot be checked: cannot derive a generator for parameter $value of type array<float, int>: array<float, int> is not supported, the keys of an array are integers or strings, not float

%sUnsupportedArrayKeyTypesTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testCasesOfAnEnumerationAsKeys
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testCasesOfAnEnumerationAsKeys cannot be checked: cannot derive a generator for parameter $value of type array<Currency, int>: array<Currency, int> is not supported, the keys of an array are integers or strings, not Currency

%sUnsupportedArrayKeyTypesTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testNullableKeys
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testNullableKeys cannot be checked: cannot derive a generator for parameter $value of type array<?int, int>: array<?int, int> is not supported, the keys of an array are integers or strings, not null

%sUnsupportedArrayKeyTypesTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testKeysOfAUnionWithFloat
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testKeysOfAUnionWithFloat cannot be checked: cannot derive a generator for parameter $value of type array<float|int, int>: array<float|int, int> is not supported, the keys of an array are integers or strings, not float

%sUnsupportedArrayKeyTypesTest.php:%d

6) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testStringOfAnIntegerAsKey
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedArrayKeyTypesTest::testStringOfAnIntegerAsKey cannot be checked: cannot derive a generator for parameter $value of type non-empty-array<'1', int>: non-empty-array<'1', int> is not supported, PHP uses the integer 1 rather than the string '1' as the key of an array

%sUnsupportedArrayKeyTypesTest.php:%d

ERRORS!
Tests: 1, Assertions: %d, Errors: 6.
