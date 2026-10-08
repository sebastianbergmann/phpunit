--TEST--
A property that cannot be checked is reported as an error when the test suite is loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testIsCombinedWithTestWith
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testIsCombinedWithTestWith cannot be checked: #[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], #[DataProviderExternal], #[TestWith], or #[TestWithJson]

%sInvalidPropertyTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testHasAParameterOfAnUnsupportedType
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testHasAParameterOfAnUnsupportedType cannot be checked: cannot derive a generator for parameter $value of type mixed: mixed is not supported

%sInvalidPropertyTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testDependsOnAnotherTest
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyTest::testDependsOnAnotherTest cannot be checked: #[Property] cannot be combined with #[Depends] or any of its variants

%sInvalidPropertyTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 3.
