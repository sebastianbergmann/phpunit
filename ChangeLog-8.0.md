# Changes in PHPUnit 8.0

All notable changes of the PHPUnit 8.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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

[8.0.0]: https://github.com/sebastianbergmann/phpunit/compare/7.5...8.0.0

