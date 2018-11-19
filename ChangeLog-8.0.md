# Changes in PHPUnit 8.0

All notable changes of the PHPUnit 8.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.0.0] - 2019-02-01

### Changed

* Implemented [#3244](https://github.com/sebastianbergmann/phpunit/issues/3244): Enable result cache by default
* Implemented [#3288](https://github.com/sebastianbergmann/phpunit/issues/3288): The `void_return` fixer of php-cs-fixer is now in effect
* Implemented [#3332](https://github.com/sebastianbergmann/phpunit/issues/3332): Deprecate annotation(s) for expecting exceptions
* Implemented [#3338](https://github.com/sebastianbergmann/phpunit/issues/3338): Deprecate assertions (and helper methods) that operate on (non-public) attributes
* Implemented [#3341](https://github.com/sebastianbergmann/phpunit/issues/3341): Deprecate optional parameters of `assertEquals()` and `assertNotEquals()`
* Implemented [#3369](https://github.com/sebastianbergmann/phpunit/issues/3369): Deprecate `assertInternalType()` and `assertNotInternalType()`

### Removed

* Implemented [#2762](https://github.com/sebastianbergmann/phpunit/issues/2762): Drop support for PHP 7.1
* Implemented [#3123](https://github.com/sebastianbergmann/phpunit/issues/3123): Remove `PHPUnit_Framework_MockObject_MockObject`

[8.0.0]: https://github.com/sebastianbergmann/phpunit/compare/7.5...8.0.0

