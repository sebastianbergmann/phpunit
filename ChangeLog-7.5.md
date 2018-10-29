# Changes in PHPUnit 7.5

All notable changes of the PHPUnit 7.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.5.0] - 2018-12-07

### Added

* Implemented [#3340](https://github.com/sebastianbergmann/phpunit/issues/3340): Added `assertEqualsCanonicalizing()`, `assertEqualsIgnoringCase()`, `assertEqualsWithDelta()`, `assertNotEqualsCanonicalizing()`, `assertNotEqualsIgnoringCase()`, and `assertNotEqualsWithDelta()` as alternatives to using `assertEquals()` and `assertNotEquals()` with the `$delta`, `$canonicalize`, or `$ignoreCase` parameters
* Implemented [#3368](https://github.com/sebastianbergmann/phpunit/issues/3368): Added `assertIsArray()`, `assertIsBool()`, `assertIsFloat()`, `assertIsInt()`, `assertIsNumeric()`, `assertIsObject()`, `assertIsResource()`, `assertIsString()`, `assertIsScalar()`, `assertIsCallable()`, `assertIsIterable()`, `assertIsNotArray()`, `assertIsNotBool()`, `assertIsNotFloat()`, `assertIsNotInt()`, `assertIsNotNumeric()`, `assertIsNotObject()`, `assertIsNotResource()`, `assertIsNotString()`, `assertIsNotScalar()`, `assertIsNotCallable()`, `assertIsNotIterable()` as alternatives to `assertInternalType()` and `assertNotInternalType()`

### Deprecated

* The optional parameters `$delta`, `$maxDepth`, `$canonicalize`, and `$ignoreCase` of `assertEquals()`, and `assertNotEquals` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these parameters will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these parameters will be removed.
* The methods `assertInternalType()` and `assertNotInternalType()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these methods will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these methods will be removed.
* The methods `assertAttributeContains()`, `assertAttributeNotContains()`, `assertAttributeContainsOnly()`, `assertAttributeNotContainsOnly()`, `assertAttributeCount()`, `assertAttributeNotCount()`, `assertAttributeEquals()`, `assertAttributeNotEquals()`, `assertAttributeEmpty()`, `assertAttributeNotEmpty()`, `assertAttributeGreaterThan()`, `assertAttributeGreaterThanOrEqual()`, `assertAttributeLessThan()`, `assertAttributeLessThanOrEqual()`, `assertAttributeSame()`, `assertAttributeNotSame()`, `assertAttributeInstanceOf()`, `assertAttributeNotInstanceOf()`, `assertAttributeInternalType()`, `assertAttributeNotInternalType()`, `attributeEqualTo()`, `readAttribute()`, `getStaticAttribute()`, and `getObjectAttribute()` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these methods will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these methods will be removed.
* The annotations `@expectedException`, `@expectedExceptionCode`, `@expectedExceptionMessage`, and `@expectedExceptionMessageRegExp` are now deprecated. There is no behavioral change in this version of PHPUnit. Using these annotations will trigger a deprecation warning in PHPUnit 8 and in PHPUnit 9 these annotations will be removed.

[7.5.0]: https://github.com/sebastianbergmann/phpunit/compare/7.4...7.5.0

