# Changes in PHPUnit 7.4

All notable changes of the PHPUnit 7.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.4.3] - 2018-10-23

### Changed

* Use `^3.1` of `sebastian/environment` again due to [regression](https://github.com/sebastianbergmann/environment/issues/31)

## [7.4.2] - 2018-10-23

### Fixed

* Fixed [#3354](https://github.com/sebastianbergmann/phpunit/pull/3354): Regression when `columns="max"` is used

## [7.4.1] - 2018-10-18

### Fixed

* Fixed [#3321](https://github.com/sebastianbergmann/phpunit/pull/3321): Incorrect TestDox output for data provider with indexed array
* Fixed [#3353](https://github.com/sebastianbergmann/phpunit/issues/3353): Requesting less than 16 columns of output results in an error

## [7.4.0] - 2018-10-05

### Added

* Implemented [#3127](https://github.com/sebastianbergmann/phpunit/issues/3127): Emit error when mocked method is not really mocked
* Implemented [#3224](https://github.com/sebastianbergmann/phpunit/pull/3224): Ability to enforce a time limit for tests not annotated with `@small`, `@medium`, or `@large`
* Implemented [#3272](https://github.com/sebastianbergmann/phpunit/issues/3272): Ability to generate code coverage whitelist filter script for Xdebug
* Implemented [#3284](https://github.com/sebastianbergmann/phpunit/issues/3284): Ability to reorder tests based on execution time
* Implemented [#3290](https://github.com/sebastianbergmann/phpunit/issues/3290): Ability to load a PHP script before any code of PHPUnit itself is loaded

[7.4.3]: https://github.com/sebastianbergmann/phpunit/compare/7.4.2...7.4.3
[7.4.2]: https://github.com/sebastianbergmann/phpunit/compare/7.4.1...7.4.2
[7.4.1]: https://github.com/sebastianbergmann/phpunit/compare/7.4.0...7.4.1
[7.4.0]: https://github.com/sebastianbergmann/phpunit/compare/7.3...7.4.0

