# Changes in PHPUnit 6.0

All notable changes of the PHPUnit 6.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.0.13] - 2017-04-03

### Fixed

* Fixed [#2638](https://github.com/sebastianbergmann/phpunit/pull/2638): Regression in `PHPUnit\Framework\TestCase:registerMockObjectsFromTestArguments()`

## [6.0.12] - 2017-04-02

### Fixed

* Fixed [#2145](https://github.com/sebastianbergmann/phpunit/issues/2145): `--stop-on-failure` fails to stop on PHP 7
* Fixed [#2448](https://github.com/sebastianbergmann/phpunit/issues/2448): Weird error when trying to run `Test` from `Test.php` but `Test.php` does not exist
* Fixed [#2572](https://github.com/sebastianbergmann/phpunit/issues/2572): `PHPUnit\Framework\TestCase:.registerMockObjectsFromTestArguments()` does not correctly handle arrays that reference themselves

## [6.0.11] - 2017-03-29

### Fixed

* Fixed [#2584](https://github.com/sebastianbergmann/phpunit/issues/2584): Wrong path to `eval-stdin.php`

## [6.0.10] - 2017-03-19

### Fixed

* Fixed [#2016](https://github.com/sebastianbergmann/phpunit/issues/2016): `prophesize()` does not work when static attributes are backed up
* Fixed [#2568](https://github.com/sebastianbergmann/phpunit/issues/2568): `ArraySubsetConstraint` uses invalid cast to array
* Fixed [#2573](https://github.com/sebastianbergmann/phpunit/issues/2573): `getMockFromWsdl()` does not handle URLs with query parameters
* `PHPUnit\Util\Test::getDataFromTestWithAnnotation()` raises notice when docblock contains Windows line endings

## [6.0.9] - 2017-03-15

### Fixed

* Fixed [#2547](https://github.com/sebastianbergmann/phpunit/issues/2547): Code Coverage data is collected for test annotated with `@coversNothing`
* Fixed [#2558](https://github.com/sebastianbergmann/phpunit/issues/2558): `countOf()` function is missing

## [6.0.8] - 2017-03-02

### Changed

* The `--check-version` commandline option is now also available when PHPUnit is installed using Composer

### Fixed

* Fixed [#1999](https://github.com/sebastianbergmann/phpunit/issues/1999): Handler is inherited from previous custom option with handler
* Fixed [#2149](https://github.com/sebastianbergmann/phpunit/issues/2149): `assertCount()` does not handle generators properly
* Fixed [#2478](https://github.com/sebastianbergmann/phpunit/issues/2478): Tests that take too long are not reported as risky test
* Fixed [#2527](https://github.com/sebastianbergmann/phpunit/issues/2527): Output of `--check-version` suggests removed `--self-upgrade`

## [6.0.7] - 2017-02-19

### Fixed

* Fixed [#2489](https://github.com/sebastianbergmann/phpunit/issues/2489): `processUncoveredFilesFromWhitelist` is not handled correctly
* Fixed default values for `addUncoveredFilesFromWhitelist` and `processUncoveredFilesFromWhitelist` in `phpunit.xsd`

## [6.0.6] - 2017-02-08

### Fixed

* Fixed [#2474](https://github.com/sebastianbergmann/phpunit/issues/2474): `--globals-backup` commandline option is not recognized
* Fixed [#2475](https://github.com/sebastianbergmann/phpunit/pull/2475): Defining a test suite with only one file does not work
* Fixed [#2487](https://github.com/sebastianbergmann/phpunit/pull/2487): Wrong default values for `backupGlobals` and `beStrictAboutTestsThatDoNotTestAnything` in `phpunit.xsd`

## [6.0.5] - 2017-02-05

### Fixed

* Deprecation errors when used with PHP 7.2

## [6.0.4] - 2017-02-04

### Fixed

* Fixed [#2470](https://github.com/sebastianbergmann/phpunit/issues/2470): PHPUnit 6.0 does not work with PHP 7.0.0-7.0.12

## [6.0.3] - 2017-02-04

### Fixed

* Fixed [#2460](https://github.com/sebastianbergmann/phpunit/issues/2460): Strange error in tests after update to PHPUnit 6
* Fixed [#2467](https://github.com/sebastianbergmann/phpunit/issues/2467): Process Isolation does not work when using PHPUnit from PHAR

## [6.0.2] - 2017-02-03

### Fixed

* Fixed [#2463](https://github.com/sebastianbergmann/phpunit/issues/2463): Whitelisting for code coverage does not work

## [6.0.1] - 2017-02-03

### Fixed

* Fixed [#2461](https://github.com/sebastianbergmann/phpunit/issues/2461): Performance regression in PHPUnit 6.0
* Fixed [#2462](https://github.com/sebastianbergmann/phpunit/issues/2462): Code Coverage whitelist is filled even if no code coverage data is to be collected

## [6.0.0] - 2017-02-03

### Added

* Merged [#2230](https://github.com/sebastianbergmann/phpunit/pull/2230): Add `getExpectedExceptionCode()` and `getExpectedExceptionMessage()`
* Merged [#2344](https://github.com/sebastianbergmann/phpunit/pull/2344): Add support for annotations on traits
* Merged [#2351](https://github.com/sebastianbergmann/phpunit/pull/2351): Allow to filter for multiple test suites
* Added the `PHPUnit\Framework\TestCase::createTestProxy()` method for creating test proxies
* Added the `--dont-report-useless-tests` commandline option
* Added the `--globals-backup` commandline option
* Added `verbatim` attribute to prevent `"true"` and `"false"` from being converted to `true` and `false`, respectively

### Changed

* PHPUnit's units of code are now namespaced
* PHPUnit is now strict about useless tests by default
* The configuration generated using `--generate-configuration` now includes `forceCoversAnnotation="true"`
* Global and super-global variables are no longer backed up before and restored after each test by default
* `PHPUnit\Framework\Assert::fail()` now increments the assertion counter
* `setUpBeforeClass()` is now invoked after all methods annotated with `@beforeClass`
* `setUp()` is now invoked after all methods annotated with `@before`
* Added `addWarning()` method to `PHPUnit\Framework\TestListener` interface
* The logfile format generated using the `--log-junit` option and the `<log type="junit" target="..."/>` configuration directive has been updated to match the [current format used by JUnit](http://llg.cubic.org/docs/junit/). Due to this change you may need to update how your continuous integration server processes test result logfiles generated by PHPUnit.
* The binary PHAR is now binary-only and cannot be used as a library anymore
* Renamed the `PHPUnit_Extensions_PhptTestCase` class to `PHPUnit_Runner_PhptTestCase`
* The `PHPUnit\Framework\TestCase::getMockObjectGenerator()` method is now private
* Merged [#2241](https://github.com/sebastianbergmann/phpunit/pull/2241): Make JSON assertions stricter
* The test runner now exits with `1` (instead of `0`) when all tests pass but there are warnings

### Removed

* Removed `PHPUnit\Framework\TestCase::getMock()` (deprecated in PHPUnit 5.4)
* Removed `PHPUnit\Framework\TestCase::getMockWithoutInvokingTheOriginalConstructor()` (deprecated in PHPUnit 5.4)
* Removed `PHPUnit\Framework\TestCase::setExpectedException()` (deprecated in PHPUnit 5.2)
* Removed `PHPUnit\Framework\TestCase::setExpectedExceptionRegExp()` (deprecated in PHPUnit 5.6)
* Removed `PHPUnit\Framework\TestCase::hasPerformedExpectationsOnOutput()` (deprecated in PHPUnit 4.3)
* Removed the `PHPUnit_Extensions_GroupTestSuite` class
* Removed the `PHPUnit_Extensions_PhptTestSuite` class
* Removed the `PHPUnit_Extensions_RepeatedTest` class
* Removed the `PHPUnit_Extensions_TestDecorator` class
* Removed the `PHPUnit_Extensions_TicketListener` class
* Removed the `PHPUnit_Util_Log_JSON` class
* Removed the `PHPUnit_Util_Log_TAP` class
* Removed the `PHPUnit_Util_Test::getTickets()` method
* Removed the `checkForUnintentionallyCoveredCode` configuration setting (deprecated in PHPUnit 5.2)
* Removed the `--log-json` commandline option (deprecated in PHPUnit 5.7)
* Removed the `--log-tap` and `--tap` commandline options (deprecated in PHPUnit 5.7)
* Removed the `--no-globals-backup` commandline option
* Removed the `--report-useless-tests` commandline option
* Removed the `--self-update` and `--self-upgrade` commandline options (deprecated in PHPUnit 5.7)
* DbUnit is no longer bundled in the PHAR distribution of PHPUnit
* PHPUnit is no longer supported on PHP 5.6

[6.0.13]: https://github.com/sebastianbergmann/phpunit/compare/6.0.12...6.0.13
[6.0.12]: https://github.com/sebastianbergmann/phpunit/compare/6.0.11...6.0.12
[6.0.11]: https://github.com/sebastianbergmann/phpunit/compare/6.0.10...6.0.11
[6.0.10]: https://github.com/sebastianbergmann/phpunit/compare/6.0.9...6.0.10
[6.0.9]: https://github.com/sebastianbergmann/phpunit/compare/6.0.8...6.0.9
[6.0.8]: https://github.com/sebastianbergmann/phpunit/compare/6.0.7...6.0.8
[6.0.7]: https://github.com/sebastianbergmann/phpunit/compare/6.0.6...6.0.7
[6.0.6]: https://github.com/sebastianbergmann/phpunit/compare/6.0.5...6.0.6
[6.0.5]: https://github.com/sebastianbergmann/phpunit/compare/6.0.4...6.0.5
[6.0.4]: https://github.com/sebastianbergmann/phpunit/compare/6.0.3...6.0.4
[6.0.3]: https://github.com/sebastianbergmann/phpunit/compare/6.0.2...6.0.3
[6.0.2]: https://github.com/sebastianbergmann/phpunit/compare/6.0.1...6.0.2
[6.0.1]: https://github.com/sebastianbergmann/phpunit/compare/6.0.0...6.0.1
[6.0.0]: https://github.com/sebastianbergmann/phpunit/compare/5.7...6.0.0

