# Changes in PHPUnit 4.8

All notable changes of the PHPUnit 4.8 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.8.6] - 2015-08-24

### Fixed

* Fixed [#1835](https://github.com/sebastianbergmann/phpunit/issues/1835): Skipped test reported as errored since PHPUnit 4.7.4

## [4.8.5] - 2015-08-19

### Fixed

* Fixed [#1831](https://github.com/sebastianbergmann/phpunit/issues/1831): PHAR manifest is missing

## [4.8.4] - 2015-08-15

### Fixed

* Fixed [#1823](https://github.com/sebastianbergmann/phpunit/issues/1823): Columns attribute in XML configuration file is ignored

## [4.8.3] - 2015-08-10

### Changed

* PHPUnit now exits early during bootstrap when an unsupported version of PHP is used

## [4.8.2] - 2015-08-07

### Fixed

* Fixed [#1816](https://github.com/sebastianbergmann/phpunit/issues/1816): PHPUnit 4.8.1 shows "4.8.0" as version number

## [4.8.1] - 2015-08-07

### Fixed

* Fixed [#1815](https://github.com/sebastianbergmann/phpunit/issues/1815): `phpunit --self-update` does not work in PHPUnit 4.8.0

## [4.8.0] - 2015-08-07

### Added

* Added `--check-version` commandline switch to check whether the current version of PHPUnit is used (PHAR only)
* Added `--no-coverage` commandline switch to ignore code coverage configuration from the configuration file
* Implemented [#1663](https://github.com/sebastianbergmann/phpunit/issues/1663): The Crap4J report's threshold is now configurable
* Merged [#1728](https://github.com/sebastianbergmann/phpunit/issues/1728): Implemented the `@testWith` annotation as "syntactic sugar" for data providers
* Merged [#1739](https://github.com/sebastianbergmann/phpunit/issues/1739): Added support to the commandline test runner for using options after arguments

### Changed

* Made the argument check of `assertContains()` and `assertNotContains()` more strict to prevent undefined behavior such as [#1808](https://github.com/sebastianbergmann/phpunit/issues/1808)
* Changed the name of the default group from `__nogroup__` to `default`

[4.8.6]: https://github.com/sebastianbergmann/phpunit/compare/4.8.5...4.8.6
[4.8.5]: https://github.com/sebastianbergmann/phpunit/compare/4.8.4...4.8.5
[4.8.4]: https://github.com/sebastianbergmann/phpunit/compare/4.8.3...4.8.4
[4.8.3]: https://github.com/sebastianbergmann/phpunit/compare/4.8.2...4.8.3
[4.8.2]: https://github.com/sebastianbergmann/phpunit/compare/4.8.1...4.8.2
[4.8.1]: https://github.com/sebastianbergmann/phpunit/compare/4.8.0...4.8.1
[4.8.0]: https://github.com/sebastianbergmann/phpunit/compare/4.7...4.8.0

