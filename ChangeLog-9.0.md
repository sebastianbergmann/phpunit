# Changes in PHPUnit 9.0

All notable changes of the PHPUnit 9.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.0.0] - 2020-02-07

### Changed

* Implemented [3746](https://github.com/sebastianbergmann/phpunit/issues/3746): Improve developer experience of global wrapper functions for assertions

### Removed

* Implemented [#3333](https://github.com/sebastianbergmann/phpunit/issues/3333): Remove annotation(s) for expecting exceptions
* Implemented [#3334](https://github.com/sebastianbergmann/phpunit/issues/3334): Drop support for PHP 7.2
* Implemented [#3339](https://github.com/sebastianbergmann/phpunit/issues/3339): Remove assertions (and helper methods) that operate on (non-public) attributes
* Implemented [#3342](https://github.com/sebastianbergmann/phpunit/issues/3342): Remove optional parameters of `assertEquals()` and `assertNotEquals()`
* Implemented [#3426](https://github.com/sebastianbergmann/phpunit/issues/3426): Clean up `assertContains()` and `assertNotContains()`

[9.0.0]: https://github.com/sebastianbergmann/phpunit/compare/8.5...master

