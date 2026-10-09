--TEST--
A property that combines #[Property] with a data provider, has a variadic parameter, specifies a generator method that does not exist or is not static, or has a parameter type that cannot be parsed is reported as an error when the test suite is loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidPropertyDeclarationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There were 5 PHPUnit errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testIsCombinedWithADataProvider
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testIsCombinedWithADataProvider cannot be checked: #[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], #[DataProviderExternal], #[TestWith], or #[TestWithJson]

%sInvalidPropertyDeclarationsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testHasAVariadicParameter
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testHasAVariadicParameter cannot be checked: variadic parameter $values is not supported

%sInvalidPropertyDeclarationsTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testSpecifiesAGeneratorMethodThatDoesNotExist
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testSpecifiesAGeneratorMethodThatDoesNotExist cannot be checked: the generator for parameter $value is specified as PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::doesNotExist(), which does not exist

%sInvalidPropertyDeclarationsTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testSpecifiesAGeneratorMethodThatIsNotStatic
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testSpecifiesAGeneratorMethodThatIsNotStatic cannot be checked: the generator for parameter $value is specified as PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::nonStaticGenerator(), which is not public and static

%sInvalidPropertyDeclarationsTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testHasAParameterWithAnUnparsableType
Property PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest::testHasAParameterWithAnUnparsableType cannot be checked: cannot derive a generator for parameter $value, the type in its @param tag cannot be parsed: unexpected "$value" at offset 10, expected ">"

%sInvalidPropertyDeclarationsTest.php:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\PropertyBasedTesting\InvalidPropertyDeclarationsTest".

No tests executed!
