# Changes in PHPUnit 4.7

All notable changes of the PHPUnit 4.7 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.7.7] - 2015-07-13

New PHAR release due to updated dependencies

## [4.7.6] - 2015-06-30

### Fixed

* Fixed [#1681](https://github.com/sebastianbergmann/phpunit/issues/1681): Code Coverage filter configuration is not passed to child processes
* Fixed [#1692](https://github.com/sebastianbergmann/phpunit/issues/1692): Clean up `PHPUnit_Extensions_RepeatedTest` after refactoring
* Fixed [#1763](https://github.com/sebastianbergmann/phpunit/issues/1763): `@before` and `@after` annotations do not work when inherited

## [4.7.5] - 2015-06-21

### Fixed

* Fixed [#490](https://github.com/sebastianbergmann/phpunit/issues/490): Ensure that a test can only be one of `@small`, `@medium`, or `@large`.
* Fixed [#1704](https://github.com/sebastianbergmann/phpunit/issues/1704): Output printed during test missing when using TAP

## [4.7.4] - 2015-06-18

### Changed

* The `PHPUnit_Framework_Constraint_IsType` constraint now knows about the `real` type (which is an alias for `float`)
* Various work on compatibility with PHP 7

### Fixed

* Fixed [#1749](https://github.com/sebastianbergmann/phpunit/issues/1749): `stopOnError` configuration setting does not work

## [4.7.3] - 2015-06-11

### Fixed

* Fixed [#1317](https://github.com/sebastianbergmann/phpunit/issues/1317): JUnit XML logfiles does not contain warnings
* Fixed an [issue](https://github.com/sebastianbergmann/php-code-coverage/issues/347) where the warning that no whitelist is used is displayed when it should not

## [4.7.2] - 2015-06-06

New PHAR release due to updated dependencies

## [4.7.1] - 2015-06-05

New PHAR release due to updated dependencies

## [4.7.0] - 2015-06-05

### Added

* Merged [#1718](https://github.com/sebastianbergmann/phpunit/issues/1718): Support for `--INI--` section in PHPT tests

### Changed

* Tests not annotated with `@small`, `@medium`, or `@large` are no longer treated as being annotated with `@small`
* In verbose mode, the test runner now prints information about the PHP runtime
* To be consistent with the printing of PHP runtime information, the configuration file used is only printed in verbose mode
* A warning is now printed when code coverage data is collected but no whitelist is configured

[4.7.7]: https://github.com/sebastianbergmann/phpunit/compare/4.7.6...4.7.7
[4.7.6]: https://github.com/sebastianbergmann/phpunit/compare/4.7.5...4.7.6
[4.7.5]: https://github.com/sebastianbergmann/phpunit/compare/4.7.4...4.7.5
[4.7.4]: https://github.com/sebastianbergmann/phpunit/compare/4.7.3...4.7.4
[4.7.3]: https://github.com/sebastianbergmann/phpunit/compare/4.7.2...4.7.3
[4.7.2]: https://github.com/sebastianbergmann/phpunit/compare/4.7.1...4.7.2
[4.7.1]: https://github.com/sebastianbergmann/phpunit/compare/4.7.0...4.7.1
[4.7.0]: https://github.com/sebastianbergmann/phpunit/compare/4.6...4.7.0

