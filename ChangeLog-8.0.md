# Changes in PHPUnit 8.0

All notable changes of the PHPUnit 8.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.0.6] - 2019-03-26

### Fixed

* Fixed [#3564](https://github.com/sebastianbergmann/phpunit/issues/3564): Production code uses class from test suite's fixture

## [8.0.5] - 2019-03-16

### Fixed

* Fixed [#3480](https://github.com/sebastianbergmann/phpunit/issues/3480): Wrong return type declaration for `TestCase::getExpectedExceptionMessage()` and `TestCase::getExpectedExceptionMessageRegExp()`
* Fixed [#3532](https://github.com/sebastianbergmann/phpunit/issues/3532): Wrong default value for `cacheResult` in `phpunit.xsd`
* Fixed [#3539](https://github.com/sebastianbergmann/phpunit/issues/3539): Wrong default value for `resolveDependencies` in `phpunit.xsd`
* Fixed [#3550](https://github.com/sebastianbergmann/phpunit/issues/3550): Check for valid attribute names in `assertObjectHasAttribute()` is too strict
* Fixed [#3555](https://github.com/sebastianbergmann/phpunit/issues/3555): Extension loader only allows objects that implement `TestHook` but should also allow `Hook`
* Fixed [#3560](https://github.com/sebastianbergmann/phpunit/issues/3560): TestDox does not work when tests are filtered

## [8.0.4] - 2019-02-18

### Fixed

* Fixed [#3530](https://github.com/sebastianbergmann/phpunit/issues/3530): `generateClassFromWsdl()` does not handle methods with multiple output values
* Fixed [#3531](https://github.com/sebastianbergmann/phpunit/issues/3531): Test suite fails on warning
* Fixed [#3534](https://github.com/sebastianbergmann/phpunit/pull/3534): Wrong message in `ConstraintTestCase`
* Fixed [#3535](https://github.com/sebastianbergmann/phpunit/issues/3535): `TypeError` in `Command`

## [8.0.3] - 2019-02-15

### Fixed

* Fixed [#3011](https://github.com/sebastianbergmann/phpunit/issues/3011): Unsupported PHPT `--SECTION--` throws unhandled exception
* Fixed [#3461](https://github.com/sebastianbergmann/phpunit/issues/3461): `StringEndsWith` matches too loosely
* Fixed [#3515](https://github.com/sebastianbergmann/phpunit/issues/3515): Random order seed is only printed in verbose mode
* Fixed [#3517](https://github.com/sebastianbergmann/phpunit/issues/3517): Useless error message when depending on test that does not exist

## [8.0.2] - 2019-02-07

### Fixed

* Fixed [#3352](https://github.com/sebastianbergmann/phpunit/issues/3352): Using `phpunit.phar` with PHPDBG does not work with `auto_globals_jit=On`
* Fixed [#3508](https://github.com/sebastianbergmann/phpunit/pull/3508): `TypeError` in `Fileloader` when trying to load nonexistent file
* Fixed [#3511](https://github.com/sebastianbergmann/phpunit/issues/3511): Asserting that an object is contained in an `iterable` while using `==` instead of `===` is no longer possible

## [8.0.1] - 2019-02-03

### Fixed

* Fixed [#3509](https://github.com/sebastianbergmann/phpunit/issues/3509): Process Isolation does not work with `phpunit.phar`

## [8.0.0] - 2019-02-01

### Changed

* Implemented [#3060](https://github.com/sebastianbergmann/phpunit/issues/3060): Cleanup `PHPUnit\Framework\Constraint\Constraint`
* Implemented [#3133](https://github.com/sebastianbergmann/phpunit/issues/3133): Enable dependency resolution by default
* Implemented [#3236](https://github.com/sebastianbergmann/phpunit/issues/3236): Define which parts of PHPUnit are covered by the backward compatibility promise
* Implemented [#3244](https://github.com/sebastianbergmann/phpunit/issues/3244): Enable result cache by default
* Implemented [#3288](https://github.com/sebastianbergmann/phpunit/issues/3288): The `void_return` fixer of php-cs-fixer is now in effect
* Implemented [#3439](https://github.com/sebastianbergmann/phpunit/pull/3439): Improve colorization of TestDox output
* Implemented [#3444](https://github.com/sebastianbergmann/phpunit/pull/3444): Consider data provider that provides data with duplicate keys to be invalid
* Implemented [#3467](https://github.com/sebastianbergmann/phpunit/pull/3467): Code location hints for `@requires` annotations as well as `--SKIPIF--`, `--EXPECT--`, `--EXPECTF--`, `--EXPECTREGEX--`, and `--{SECTION}_EXTERNAL--` sections of PHPT tests
* Implemented [#3481](https://github.com/sebastianbergmann/phpunit/pull/3481): Improved `--help` output

### Deprecated

* Implemented [#3332](https://github.com/sebastianbergmann/phpunit/issues/3332): Deprecate annotation(s) for expecting exceptions
* Implemented [#3338](https://github.com/sebastianbergmann/phpunit/issues/3338): Deprecate assertions (and helper methods) that operate on (non-public) attributes
* Implemented [#3341](https://github.com/sebastianbergmann/phpunit/issues/3341): Deprecate optional parameters of `assertEquals()` and `assertNotEquals()`
* Implemented [#3369](https://github.com/sebastianbergmann/phpunit/issues/3369): Deprecate `assertInternalType()` and `assertNotInternalType()`
* Implemented [#3388](https://github.com/sebastianbergmann/phpunit/issues/3388): Deprecate the `TestListener` interface
* Implemented [#3425](https://github.com/sebastianbergmann/phpunit/issues/3425): Deprecate optional parameters of `assertContains()` and `assertNotContains()` as well as using these methods with `string` haystacks
* Implemented [#3494](https://github.com/sebastianbergmann/phpunit/issues/3494): Deprecate `assertArraySubset()`

### Removed

* Implemented [#2762](https://github.com/sebastianbergmann/phpunit/issues/2762): Drop support for PHP 7.1
* Implemented [#3123](https://github.com/sebastianbergmann/phpunit/issues/3123): Remove `PHPUnit_Framework_MockObject_MockObject`

[8.0.6]: https://github.com/sebastianbergmann/phpunit/compare/8.0.5...8.0.6
[8.0.5]: https://github.com/sebastianbergmann/phpunit/compare/8.0.4...8.0.5
[8.0.4]: https://github.com/sebastianbergmann/phpunit/compare/8.0.3...8.0.4
[8.0.3]: https://github.com/sebastianbergmann/phpunit/compare/8.0.2...8.0.3
[8.0.2]: https://github.com/sebastianbergmann/phpunit/compare/8.0.1...8.0.2
[8.0.1]: https://github.com/sebastianbergmann/phpunit/compare/8.0.0...8.0.1
[8.0.0]: https://github.com/sebastianbergmann/phpunit/compare/7.5...8.0.0

