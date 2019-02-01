# Changes in PHPUnit 7.5

All notable changes of the PHPUnit 7.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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

[7.5.3]: https://github.com/sebastianbergmann/phpunit/compare/7.5.2...7.5.3
[7.5.2]: https://github.com/sebastianbergmann/phpunit/compare/7.5.1...7.5.2
[7.5.1]: https://github.com/sebastianbergmann/phpunit/compare/7.5.0...7.5.1
[7.5.0]: https://github.com/sebastianbergmann/phpunit/compare/7.4.5...7.5.0

