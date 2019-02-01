# Changes in PHPUnit 6.5

All notable changes of the PHPUnit 6.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.5.14] - 2019-02-01

### Fixed

* Fixed [#3459](https://github.com/sebastianbergmann/phpunit/issues/3459): `@requires` function swallows digits at the end of function name

## [6.5.13] - 2018-09-08

### Fixed

* Fixed [#3181](https://github.com/sebastianbergmann/phpunit/issues/3181): `--filter` should be case-insensitive
* Fixed [#3234](https://github.com/sebastianbergmann/phpunit/issues/3234): `assertArraySubset()` with `$strict=true` does not display differences properly
* Fixed [#3254](https://github.com/sebastianbergmann/phpunit/issues/3254): TextUI test runner cannot run a `Test` instance that is not a `TestSuite`

## [6.5.12] - 2018-08-22

### Fixed

* Fixed [#3248](https://github.com/sebastianbergmann/phpunit/issues/3248) and [#3233](https://github.com/sebastianbergmann/phpunit/issues/3233): `phpunit.xsd` dictates element order where it should not
* Fixed [#3251](https://github.com/sebastianbergmann/phpunit/issues/3251): TeamCity result logger is missing test duration information

## [6.5.11] - 2018-08-07

### Fixed

* Fixed [#3219](https://github.com/sebastianbergmann/phpunit/issues/3219): `getMockFromWsdl()` generates invalid PHP code when WSDL filename contains special characters

## [6.5.10] - 2018-08-03

### Fixed

* Fixed [#3209](https://github.com/sebastianbergmann/phpunit/issues/3209): `Test::run()` and `TestCase::run()` interface contradiction
* Fixed [#3218](https://github.com/sebastianbergmann/phpunit/issues/3218): `prefix` attribute for `directory` node missing from `phpunit.xml` XSD
* Fixed [#3225](https://github.com/sebastianbergmann/phpunit/issues/3225): `coverage-php` missing from `phpunit.xsd`

## [6.5.9] - 2018-07-03

### Fixed

* Fixed [#3142](https://github.com/sebastianbergmann/phpunit/issues/3142): Method-level annotations (`@backupGlobals`, `@backupStaticAttributes`, `@errorHandler`, `@preserveGlobalState`) do not override class-level annotations

## [6.5.8] - 2018-04-10

### Fixed

* Fixed [#2830](https://github.com/sebastianbergmann/phpunit/issues/2830): `@runClassInSeparateProcess` does not work for tests that use `@dataProvider`

## [6.5.7] - 2018-02-26

### Fixed

* Fixed [#2974](https://github.com/sebastianbergmann/phpunit/issues/2974): JUnit XML logfile contains invalid characters when test output contains binary data

## [6.5.6] - 2018-02-01

### Fixed

* Fixed [#2236](https://github.com/sebastianbergmann/phpunit/issues/2236): Exceptions in `tearDown()` do not affect `getStatus()`
* Fixed [#2950](https://github.com/sebastianbergmann/phpunit/issues/2950): Class extending `PHPUnit\Framework\TestSuite` does not extend `PHPUnit\FrameworkTestCase`
* Fixed [#2972](https://github.com/sebastianbergmann/phpunit/issues/2972): PHPUnit crashes when test suite contains both `.phpt` files and unconventionally named tests

## [6.5.5] - 2017-12-17

### Fixed

* Fixed [#2922](https://github.com/sebastianbergmann/phpunit/issues/2922): Test class is not discovered when there is a test class with `@group` and provider throwing exception in it, tests are run with `--exclude-group` for that group, there is another class called later (after the class from above), and the name of that another class does not match its filename

## [6.5.4] - 2017-12-10

### Changed

* Require version 5.0.5 of `phpunit/phpunit-mock-objects` for [phpunit-mock-objects#394](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/394)

## [6.5.3] - 2017-12-06

### Fixed

* Fixed an issue with PHPT tests when `forceCoversAnnotation="true"` is configured

## [6.5.2] - 2017-12-02

### Changed

* Require version 5.0.4 of `phpunit/phpunit-mock-objects` for [phpunit-mock-objects#388](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/388)

## [6.5.1] - 2017-12-01

* Fixed [#2886](https://github.com/sebastianbergmann/phpunit/pull/2886): Forced environment variables do not affect `getenv()`

## [6.5.0] - 2017-12-01

### Added

* Implemented [#2286](https://github.com/sebastianbergmann/phpunit/issues/2286): Optional `$exit` parameter for `PHPUnit\TextUI\TestRunner::run()`
* Implemented [#2496](https://github.com/sebastianbergmann/phpunit/issues/2496): Allow shallow copy of dependencies

### Fixed

* Fixed [#2654](https://github.com/sebastianbergmann/phpunit/issues/2654): Problems with `assertJsonStringEqualsJsonString()`
* Fixed [#2810](https://github.com/sebastianbergmann/phpunit/pull/2810): Code Coverage for PHPT tests does not work

[6.5.14]: https://github.com/sebastianbergmann/phpunit/compare/6.5.13...6.5.14
[6.5.13]: https://github.com/sebastianbergmann/phpunit/compare/6.5.12...6.5.13
[6.5.12]: https://github.com/sebastianbergmann/phpunit/compare/6.5.11...6.5.12
[6.5.11]: https://github.com/sebastianbergmann/phpunit/compare/6.5.10...6.5.11
[6.5.10]: https://github.com/sebastianbergmann/phpunit/compare/6.5.9...6.5.10
[6.5.9]: https://github.com/sebastianbergmann/phpunit/compare/6.5.8...6.5.9
[6.5.8]: https://github.com/sebastianbergmann/phpunit/compare/6.5.7...6.5.8
[6.5.7]: https://github.com/sebastianbergmann/phpunit/compare/6.5.6...6.5.7
[6.5.6]: https://github.com/sebastianbergmann/phpunit/compare/6.5.5...6.5.6
[6.5.5]: https://github.com/sebastianbergmann/phpunit/compare/6.5.4...6.5.5
[6.5.4]: https://github.com/sebastianbergmann/phpunit/compare/6.5.3...6.5.4
[6.5.3]: https://github.com/sebastianbergmann/phpunit/compare/6.5.2...6.5.3
[6.5.2]: https://github.com/sebastianbergmann/phpunit/compare/6.5.1...6.5.2
[6.5.1]: https://github.com/sebastianbergmann/phpunit/compare/6.5.0...6.5.1
[6.5.0]: https://github.com/sebastianbergmann/phpunit/compare/6.4...6.5.0

