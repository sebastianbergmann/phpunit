# Changes in PHPUnit 7.0

All notable changes of the PHPUnit 7.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.0.0] - 2018-02-02

### Fixed

* Fixed [#2902](https://github.com/sebastianbergmann/phpunit/issues/2902): `@test` annotation gets accepted no matter what
* Fixed [#2907](https://github.com/sebastianbergmann/phpunit/issues/2907): `StringMatchesFormatDescription` constraint does not handle escaped `%` correctly

### Removed

* Implemented [#2473](https://github.com/sebastianbergmann/phpunit/issues/2473): Drop support for PHP 7.0
* `@scenario` is no longer an alias for `@test`

[7.0.0]: https://github.com/sebastianbergmann/phpunit/compare/6.5...7.0.0

