# Changes in PHPUnit 4.8

All notable changes of the PHPUnit 4.8 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.8.25] - 2016-MM-DD

### Fixed

* Fixed [#2112](https://github.com/sebastianbergmann/phpunit/issues/2112): Output is html entity encoded when ran through `phpdbg`

## [4.8.24] - 2016-03-14

### Fixed

* Fixed [#1959](https://github.com/sebastianbergmann/phpunit/issues/1959): Prophecy errors are not handled correctly
* Fixed [#2039](https://github.com/sebastianbergmann/phpunit/issues/2039): TestDox does not handle snake_case test methods properly
* Fixed [#2109](https://github.com/sebastianbergmann/phpunit/issues/2109): Process isolation leaks global variable

## [4.8.23] - 2016-02-11

### Fixed

* Fixed [#2072](https://github.com/sebastianbergmann/phpunit/issues/2072): Paths in XML configuration file were not handled correctly when they have whitespace around them

## [4.8.22] - 2016-02-02

### Fixed

* Fixed [#2050](https://github.com/sebastianbergmann/phpunit/issues/2050): `PHPUnit_Util_XML::load()` raises exception with empty message when XML string is empty
* Fixed a bug in `PHPUnit_Runner_Version::series()`

## [4.8.21] - 2015-12-12

### Changed

* Reverted the changes introduced in PHPUnit 4.8.20 as the only thing the new version constraint in `composer.json` achieved was locking PHP 7 users to PHPUnit 4.8.19

## [4.8.20] - 2015-12-10

### Changed

* Changed PHP version constraint in `composer.json` to prevent installing PHPUnit 4.8 on PHP 7
* `phpunit.phar` will now refuse to work on PHP 7

## [4.8.19] - 2015-11-30

### Fixed

* Fixed [#1955](https://github.com/sebastianbergmann/phpunit/issues/1955): Process isolation fails when running tests with `phpdbg -qrr`

## [4.8.18] - 2015-11-11

### Changed

* DbUnit 1.4 is bundled again in the PHAR distribution

## [4.8.17] - 2015-11-10

### Fixed

* Fixed [#1935](https://github.com/sebastianbergmann/phpunit/issues/1935): `PHP_CodeCoverage_Exception` not handled properly
* Fixed [#1948](https://github.com/sebastianbergmann/phpunit/issues/1948): Unable to use PHAR due to unsupported signature error

### Changed

* DbUnit >= 2.0.2 is now bundled in the PHAR distribution

## [4.8.16] - 2015-10-23

### Added

* Implemented [#1925](https://github.com/sebastianbergmann/phpunit/issues/1925): Provide a library-only PHAR

## [4.8.15] - 2015-10-22

### Fixed

* The backup of global state is now properly restored when changes to global state are disallowed
* The `__PHPUNIT_PHAR__` constant is now properly set when the PHPUnit PHAR is used as a library

## [4.8.14] - 2015-10-17

### Fixed

* Fixed [#1892](https://github.com/sebastianbergmann/phpunit/issues/1892): `--coverage-text` does not honor color settings

## [4.8.13] - 2015-10-14

### Added

* Added the `--self-upgrade` commandline switch for upgrading a PHPUnit PHAR to the latest version

### Changed

* The `--self-update` commandline switch now updates a PHPUnit PHAR to the latest version within the same release series

## [4.8.12] - 2015-10-12

### Changed

* Merged [#1893](https://github.com/sebastianbergmann/phpunit/issues/1893): Removed workaround for phpab bug

## [4.8.11] - 2015-10-07

### Fixed

* Merged [#1885](https://github.com/sebastianbergmann/phpunit/issues/1885): Fixed handling of PHP configuration settings for process isolation
* Fixed [#1857](https://github.com/sebastianbergmann/phpunit/issues/1857): `@covers` and `@uses` should only take a single word
* Fixed [#1879](https://github.com/sebastianbergmann/phpunit/issues/1879): `assertEqualXMLStructure()` cannot compare nodes with an ID
* Fixed [#1898](https://github.com/sebastianbergmann/phpunit/issues/1898): `@covers` and `@uses` cannot be used for namespaced functions
* Fixed [#1901](https://github.com/sebastianbergmann/phpunit/issues/1901): `--self-update` updates to PHPUnit 5, even on PHP < 5.6

## [4.8.10] - 2015-10-01

### Fixed

* Merged [#1884](https://github.com/sebastianbergmann/phpunit/issues/1884): Avoid passing `Error` to `onNotSuccessfulTest()` on PHP 7

## [4.8.9] - 2015-09-20

### Fixed

* Fixed regression introduced in PHPUnit 4.8.8

## [4.8.8] - 2015-09-19

### Fixed

* Fixed [#1860](https://github.com/sebastianbergmann/phpunit/issues/1860): Not well-formed XML strings are always considered equal by `PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString()`

## [4.8.7] - 2015-09-14

New PHAR release due to updated dependencies

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

[4.8.25]: https://github.com/sebastianbergmann/phpunit/compare/4.8.24...4.8.25
[4.8.24]: https://github.com/sebastianbergmann/phpunit/compare/4.8.23...4.8.24
[4.8.23]: https://github.com/sebastianbergmann/phpunit/compare/4.8.22...4.8.23
[4.8.22]: https://github.com/sebastianbergmann/phpunit/compare/4.8.21...4.8.22
[4.8.21]: https://github.com/sebastianbergmann/phpunit/compare/4.8.20...4.8.21
[4.8.20]: https://github.com/sebastianbergmann/phpunit/compare/4.8.19...4.8.20
[4.8.19]: https://github.com/sebastianbergmann/phpunit/compare/4.8.18...4.8.19
[4.8.18]: https://github.com/sebastianbergmann/phpunit/compare/4.8.17...4.8.18
[4.8.17]: https://github.com/sebastianbergmann/phpunit/compare/4.8.16...4.8.17
[4.8.16]: https://github.com/sebastianbergmann/phpunit/compare/4.8.15...4.8.16
[4.8.15]: https://github.com/sebastianbergmann/phpunit/compare/4.8.14...4.8.15
[4.8.14]: https://github.com/sebastianbergmann/phpunit/compare/4.8.13...4.8.14
[4.8.13]: https://github.com/sebastianbergmann/phpunit/compare/4.8.12...4.8.13
[4.8.12]: https://github.com/sebastianbergmann/phpunit/compare/4.8.11...4.8.12
[4.8.11]: https://github.com/sebastianbergmann/phpunit/compare/4.8.10...4.8.11
[4.8.10]: https://github.com/sebastianbergmann/phpunit/compare/4.8.9...4.8.10
[4.8.9]: https://github.com/sebastianbergmann/phpunit/compare/4.8.8...4.8.9
[4.8.8]: https://github.com/sebastianbergmann/phpunit/compare/4.8.7...4.8.8
[4.8.7]: https://github.com/sebastianbergmann/phpunit/compare/4.8.6...4.8.7
[4.8.6]: https://github.com/sebastianbergmann/phpunit/compare/4.8.5...4.8.6
[4.8.5]: https://github.com/sebastianbergmann/phpunit/compare/4.8.4...4.8.5
[4.8.4]: https://github.com/sebastianbergmann/phpunit/compare/4.8.3...4.8.4
[4.8.3]: https://github.com/sebastianbergmann/phpunit/compare/4.8.2...4.8.3
[4.8.2]: https://github.com/sebastianbergmann/phpunit/compare/4.8.1...4.8.2
[4.8.1]: https://github.com/sebastianbergmann/phpunit/compare/4.8.0...4.8.1
[4.8.0]: https://github.com/sebastianbergmann/phpunit/compare/4.7...4.8.0

