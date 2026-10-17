--TEST--
A property with a parameter of a type for which no generator can be derived is reported as an error when the test suite is loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/UnsupportedParameterTypesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There were 9 PHPUnit errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testInterface
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testInterface cannot be checked: cannot derive a generator for parameter $value of type PHPUnit\TestFixture\PropertyBasedTesting\AnInterface: PHPUnit\TestFixture\PropertyBasedTesting\AnInterface is an interface

%sUnsupportedParameterTypesTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testAbstractClass
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testAbstractClass cannot be checked: cannot derive a generator for parameter $value of type PHPUnit\TestFixture\PropertyBasedTesting\AnAbstractClass: class PHPUnit\TestFixture\PropertyBasedTesting\AnAbstractClass is abstract

%sUnsupportedParameterTypesTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testClassWithoutPublicConstructor
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testClassWithoutPublicConstructor cannot be checked: cannot derive a generator for parameter $value of type PHPUnit\TestFixture\PropertyBasedTesting\AClassWithoutAPublicConstructor: class PHPUnit\TestFixture\PropertyBasedTesting\AClassWithoutAPublicConstructor does not have a public constructor

%sUnsupportedParameterTypesTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testObject
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testObject cannot be checked: cannot derive a generator for parameter $value of type object: object is not supported

%sUnsupportedParameterTypesTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testCallable
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testCallable cannot be checked: cannot derive a generator for parameter $value of type callable: callable is not supported

%sUnsupportedParameterTypesTest.php:%d

6) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testClosure
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testClosure cannot be checked: cannot derive a generator for parameter $value of type Closure: class Closure does not have a public constructor

%sUnsupportedParameterTypesTest.php:%d

7) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testIterable
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testIterable cannot be checked: cannot derive a generator for parameter $value of type iterable: iterable is not supported

%sUnsupportedParameterTypesTest.php:%d

8) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testResource
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testResource cannot be checked: cannot derive a generator for parameter $value of type resource: resource is not supported

%sUnsupportedParameterTypesTest.php:%d

9) PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testTemplateType
Property PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest::testTemplateType cannot be checked: cannot derive a generator for parameter $value of type T: PHPUnit\TestFixture\PropertyBasedTesting\T is neither a supported type nor the name of an existing class

%sUnsupportedParameterTypesTest.php:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\PropertyBasedTesting\UnsupportedParameterTypesTest".

No tests executed!
