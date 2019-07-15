# Changes in PHPUnit 7.5

All notable changes of the PHPUnit 7.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.5.14] - 2019-07-15

### Fixed

* Fixed [#3743](https://github.com/sebastianbergmann/phpunit/issues/3743): `EmptyIterator` instances are not handled correctly by `Count` and `IsEmpty` constraints

## [7.5.13] - 2019-06-19

### Fixed

* Fixed [#3722](https://github.com/sebastianbergmann/phpunit/issues/3722): `getObjectForTrait()` does not work for traits that declare a constructor
* Fixed [#3723](https://github.com/sebastianbergmann/phpunit/pull/3723): Unescaped dash in character group in regular expression

## [7.5.12] - 2019-05-28

### Changed

* After each test, `libxml_clear_errors()` is now called to clear the libxml error buffer

### Fixed

* Fixed [#3694](https://github.com/sebastianbergmann/phpunit/pull/3694): Constructor arguments for `Throwable` and `Exception` are ignored
* Fixed [#3709](https://github.com/sebastianbergmann/phpunit/pull/3709): Method-level `@coversNothing` annotation does not prevent code coverage data collection

## [7.5.11] - 2019-05-14

### Fixed

* Fixed [#3683](https://github.com/sebastianbergmann/phpunit/issues/3683): Regression in PHPUnit 7.5.10 with regard to Exception stubbing/mocking

## [7.5.10] - 2019-05-09

### Fixed

* Fixed [#3414](https://github.com/sebastianbergmann/phpunit/pull/3414): `willThrowException()` only accepts `Exception`, not `Throwable`
* Fixed [#3587](https://github.com/sebastianbergmann/phpunit/issues/3587): `.phpunit.result.cache` file is all over the place
* Fixed [#3596](https://github.com/sebastianbergmann/phpunit/issues/3596): Mocking an interface that extends another interface forgets to mock its own methods
* Fixed [#3674](https://github.com/sebastianbergmann/phpunit/issues/3674): `TypeError` when an incorrect file path is given

## [7.5.9] - 2019-04-19

### Fixed

* Fixed [#3607](https://github.com/sebastianbergmann/phpunit/issues/3607): Return value generation interferes with proxying to original method

## [7.5.8] - 2019-03-26

### Fixed

* Fixed [#3564](https://github.com/sebastianbergmann/phpunit/issues/3564): Production code uses class from test suite's fixture

## [7.5.7] - 2019-03-16

### Fixed

* Fixed [#3480](https://github.com/sebastianbergmann/phpunit/issues/3480): Wrong return type declaration for `TestCase::getExpectedExceptionMessage()` and `TestCase::getExpectedExceptionMessageRegExp()`
* Fixed [#3550](https://github.com/sebastianbergmann/phpunit/issues/3550): Check for valid attribute names in `assertObjectHasAttribute()` is too strict

## [7.5.6] - 2019-02-18

### Fixed

* Fixed [#3530](https://github.com/sebastianbergmann/phpunit/issues/3530): `generateClassFromWsdl()` does not handle methods with multiple output values
* Fixed [#3531](https://github.com/sebastianbergmann/phpunit/issues/3531): Test suite fails on warning
* Fixed [#3534](https://github.com/sebastianbergmann/phpunit/pull/3534): Wrong message in `ConstraintTestCase`

## [7.5.5] - 2019-02-15

### Fixed

* Fixed [#3011](https://github.com/sebastianbergmann/phpunit/issues/3011): Unsupported PHPT `--SECTION--` throws unhandled exception
* Fixed [#3461](https://github.com/sebastianbergmann/phpunit/issues/3461): `StringEndsWith` matches too loosely
* Fixed [#3515](https://github.com/sebastianbergmann/phpunit/issues/3515): Random order seed is only printed in verbose mode
* Fixed [#3517](https://github.com/sebastianbergmann/phpunit/issues/3517): Useless error message when depending on test that does not exist

## [7.5.4] - 2019-02-07

### Fixed

* Fixed [#3352](https://github.com/sebastianbergmann/phpunit/issues/3352): Using `phpunit.phar` with PHPDBG does not work with `auto_globals_jit=On`
* Fixed [#3502](https://github.com/sebastianbergmann/phpunit/issues/3502): Numeric `@ticket` or `@group` annotations no longer work

## [7.5.3] - 2019-02-01

### Fixed

* Fixed [#3490](https://github.com/sebastianbergmann/phpunit/pull/3490): Exceptions in `tearDownAfterClass()` kill PHPUnit

### Deprecated

* The method `assertArraySubset()` is now deprecated. There is no behavioral change in this version of PHPUnit. Using this method will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 this method will be removed.

## [7.5.2] - 2019-01-15

### Fixed

* Fixed [#3456](https://github.com/sebastianbergmann/phpunit/pull/3456): Generator for Xdebug filter script does not handle directories with leading `.` correctly
* Fixed [#3459](https://github.com/sebastianbergmann/phpunit/issues/3459): `@requires` function swallows digits at the end of function name

## [7.5.1] - 2018-12-12

### Fixed

* Fixed [#3441](https://github.com/sebastianbergmann/phpunit/issues/3441): Call to undefined method `DataProviderTestSuite::usesDataProvider()`

## [7.5.0] - 2018-12-07

### Added

* Implemented [#3340](https://github.com/sebastianbergmann/phpunit/issues/3340): Added `assertEqualsCanonicalizing()`, `assertEqualsIgnoringCase()`, `assertEqualsWithDelta()`, `assertNotEqualsCanonicalizing()`, `assertNotEqualsIgnoringCase()`, and `assertNotEqualsWithDelta()` as alternatives to using `assertEquals()` and `assertNotEquals()` with the `$delta`, `$canonicalize`, or `$ignoreCase` parameters
* Implemented [#3368](https://github.com/sebastianbergmann/phpunit/issues/3368): Added `assertIsArray()`, `assertIsBool()`, `assertIsFloat()`, `assertIsInt()`, `assertIsNumeric()`, `assertIsObject()`, `assertIsResource()`, `assertIsString()`, `assertIsScalar()`, `assertIsCallable()`, `assertIsIterable()`, `assertIsNotArray()`, `assertIsNotBool()`, `assertIsNotFloat()`, `assertIsNotInt()`, `assertIsNotNumeric()`, `assertIsNotObject()`, `assertIsNotResource()`, `assertIsNotString()`, `assertIsNotScalar()`, `assertIsNotCallable()`, `assertIsNotIterable()` as alternatives to `assertInternalType()` and `assertNotInternalType()`
* Implemented [#3391](https://github.com/sebastianbergmann/phpunit/issues/3391): Added a `TestHook` that fires after each test, regardless of result
* Implemented [#3417](https://github.com/sebastianbergmann/phpunit/pull/3417): Refinements related to test suite sorting and TestDox result printer
* Implemented [#3422](https://github.com/sebastianbergmann/phpunit/issues/3422): Added `assertStringContainsString()`, `assertStringContainsStringIgnoringCase()`, `assertStringNotContainsString()`, and `assertStringNotContainsStringIgnoringCase()`

### Deprecated

* The methods `assertInternalType()` and `assertNotInternalType()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these methods will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these methods will be removed.
* The methods `assertAttributeContains()`, `assertAttributeNotContains()`, `assertAttributeContainsOnly()`, `assertAttributeNotContainsOnly()`, `assertAttributeCount()`, `assertAttributeNotCount()`, `assertAttributeEquals()`, `assertAttributeNotEquals()`, `assertAttributeEmpty()`, `assertAttributeNotEmpty()`, `assertAttributeGreaterThan()`, `assertAttributeGreaterThanOrEqual()`, `assertAttributeLessThan()`, `assertAttributeLessThanOrEqual()`, `assertAttributeSame()`, `assertAttributeNotSame()`, `assertAttributeInstanceOf()`, `assertAttributeNotInstanceOf()`, `assertAttributeInternalType()`, `assertAttributeNotInternalType()`, `attributeEqualTo()`, `readAttribute()`, `getStaticAttribute()`, and `getObjectAttribute()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these methods will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these methods will be removed.
* The optional parameters `$delta`, `$maxDepth`, `$canonicalize`, and `$ignoreCase` of `assertEquals()` and `assertNotEquals()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these parameters will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these parameters will be removed.
* The annotations `@expectedException`, `@expectedExceptionCode`, `@expectedExceptionMessage`, and `@expectedExceptionMessageRegExp` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these annotations will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these annotations will be removed.
* Using the methods `assertContains()` and `assertNotContains()` on `string` haystacks is now deprecated. There is no behavioral change in this version of PHPUnit. Using these methods on `string` haystacks will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these methods cannot be used on on `string` haystacks anymore.
* The optional parameters `$ignoreCase`, `$checkForObjectIdentity`, and `$checkForNonObjectIdentity` of `assertContains()` and `assertNotContains()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these parameters will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these parameters will be removed.

### Fixed

* Fixed [#3428](https://github.com/sebastianbergmann/phpunit/pull/3428): `TestSuite` setup failures are not logged correctly
* Fixed [#3429](https://github.com/sebastianbergmann/phpunit/pull/3429): Inefficient loop in `getHookMethods()`
* Fixed [#3437](https://github.com/sebastianbergmann/phpunit/pull/3437): JUnit logger skips PHPT tests

[7.5.14]: https://github.com/sebastianbergmann/phpunit/compare/7.5.13...7.5.14
[7.5.13]: https://github.com/sebastianbergmann/phpunit/compare/7.5.12...7.5.13
[7.5.12]: https://github.com/sebastianbergmann/phpunit/compare/7.5.11...7.5.12
[7.5.11]: https://github.com/sebastianbergmann/phpunit/compare/7.5.10...7.5.11
[7.5.10]: https://github.com/sebastianbergmann/phpunit/compare/7.5.9...7.5.10
[7.5.9]: https://github.com/sebastianbergmann/phpunit/compare/7.5.8...7.5.9
[7.5.8]: https://github.com/sebastianbergmann/phpunit/compare/7.5.7...7.5.8
[7.5.7]: https://github.com/sebastianbergmann/phpunit/compare/7.5.6...7.5.7
[7.5.6]: https://github.com/sebastianbergmann/phpunit/compare/7.5.5...7.5.6
[7.5.5]: https://github.com/sebastianbergmann/phpunit/compare/7.5.4...7.5.5
[7.5.4]: https://github.com/sebastianbergmann/phpunit/compare/7.5.3...7.5.4
[7.5.3]: https://github.com/sebastianbergmann/phpunit/compare/7.5.2...7.5.3
[7.5.2]: https://github.com/sebastianbergmann/phpunit/compare/7.5.1...7.5.2
[7.5.1]: https://github.com/sebastianbergmann/phpunit/compare/7.5.0...7.5.1
[7.5.0]: https://github.com/sebastianbergmann/phpunit/compare/7.4.5...7.5.0

