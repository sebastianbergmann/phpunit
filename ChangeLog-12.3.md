# Changes in PHPUnit 12.3

All notable changes of the PHPUnit 12.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.3.8] - 2025-09-03

### Fixed

* [#6340](https://github.com/sebastianbergmann/phpunit/issues/6340): Implicitly enabled display of deprecation details is not disabled when it should be

## [12.3.7] - 2025-08-28

### Changed

* `#[IgnorePhpunitDeprecations]` is now considered for test runner deprecations (where applicable)

## [12.3.6] - 2025-08-20

### Changed

* Do not configure `report_memleaks` setting (which will be deprecated in PHP 8.5) for PHPT processes

## [12.3.5] - 2025-08-16

### Changed

* [#6319](https://github.com/sebastianbergmann/phpunit/issues/6319): Detect premature end of PHPUnit's main PHP process
* [#6321](https://github.com/sebastianbergmann/phpunit/issues/6321): Allow `error_reporting=E_ALL` for `--check-php-configuration`

### Fixed

* [#5863](https://github.com/sebastianbergmann/phpunit/issues/5863): TestDox printer does not show previous exception
* [#6102](https://github.com/sebastianbergmann/phpunit/issues/6102): `expectUserDeprecationMessage*()` fails when test is run in separate process

## [12.3.4] - 2025-08-12

### Changed

* [#6308](https://github.com/sebastianbergmann/phpunit/pull/6308): Improve output of `--check-php-configuration`
* The version number for the test result cache file has been incremented to reflect that its structure for PHPUnit 12.3 is not compatible with its structure for PHPUnit 8.5 and PHPUnit 9.6

### Fixed

* [#6197](https://github.com/sebastianbergmann/phpunit/issues/6197): `ini_set('error_log')` sets filepath outside `open_basedir`
* [#6279](https://github.com/sebastianbergmann/phpunit/issues/6279): Deprecation triggered in data provider method affects all test methods using that data provider method
* [#6281](https://github.com/sebastianbergmann/phpunit/issues/6281): Exceptions raised in after-test method are not reported for skipped tests

## [12.3.3] - 2025-08-11

### Fixed

* [#6304](https://github.com/sebastianbergmann/phpunit/issues/6304): PHPUnit 11.5.29 hangs when a test runner deprecation is triggered and process isolation is used (this reverts "`#[IgnorePhpunitDeprecations]` is now considered for test runner deprecations" from PHPUnit 12.3.1)

## [12.3.2] - 2025-08-10

### Changed

* [#6300](https://github.com/sebastianbergmann/phpunit/issues/6300): Emit warning when the name of a data provider method begins with `test`
* Do not use `SplObjectStorage` methods that will be deprecated in PHP 8.5

## [12.3.1] - 2025-08-09

### Added

* [#6297](https://github.com/sebastianbergmann/phpunit/issues/6297): `--check-php-configuration` CLI option for checking whether PHP is configured for testing

### Changed

* `#[IgnorePhpunitDeprecations]` is now considered for test runner deprecations (where applicable)

### Fixed

* [#6160](https://github.com/sebastianbergmann/phpunit/issues/6160): Baseline file in a subdirectory contains absolute paths
* [#6294](https://github.com/sebastianbergmann/phpunit/issues/6294): Silent failure of PHP fatal errors
* Errors due to invalid data provided using `#[TestWith]` or `#[TestWithJson]` attributes are now properly reported
* The `DataProviderMethodFinished` event is now also emitted when the provided data set has an invalid key

## [12.3.0] - 2025-08-01

### Added

* [#3795](https://github.com/sebastianbergmann/phpunit/issues/3795): Bootstrap scripts specific to test suites
* [#6268](https://github.com/sebastianbergmann/phpunit/pull/6268): `#[IgnorePHPUnitWarnings]` attribute for ignoring PHPUnit warnings
* `#[TestDoxFormatter]` and `#[TestDoxFormatterExternal]` attributes for configuring a custom TestDox formatter for tests that use data from data providers
* `TestRunner\ChildProcessErrored` event
* `Configuration::includeTestSuites()` and `Configuration::excludeTestSuites()`

### Changed

* [#6237](https://github.com/sebastianbergmann/phpunit/issues/6237): Do not run tests when code coverage analysis is requested but code coverage data cannot be collected
* [#6272](https://github.com/sebastianbergmann/phpunit/issues/6272): Use `@<data-set-name>` format (compatible with `--filter` CLI option) in defect messages
* [#6273](https://github.com/sebastianbergmann/phpunit/pull/6273): Warn when `#[DataProvider*]` attributes are mixed with `#[TestWith*]` attributes

### Deprecated

* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::includeTestSuite()`, use `Configuration::includeTestSuites()` instead
* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::excludeTestSuite()`, use `Configuration::excludeTestSuites()` instead
* [#6246](https://github.com/sebastianbergmann/phpunit/issues/6246): Using `#[CoversNothing]` on a test method

[12.3.8]: https://github.com/sebastianbergmann/phpunit/compare/12.3.7...12.3.8
[12.3.7]: https://github.com/sebastianbergmann/phpunit/compare/12.3.6...12.3.7
[12.3.6]: https://github.com/sebastianbergmann/phpunit/compare/12.3.5...12.3.6
[12.3.5]: https://github.com/sebastianbergmann/phpunit/compare/12.3.4...12.3.5
[12.3.4]: https://github.com/sebastianbergmann/phpunit/compare/12.3.3...12.3.4
[12.3.3]: https://github.com/sebastianbergmann/phpunit/compare/12.3.2...12.3.3
[12.3.2]: https://github.com/sebastianbergmann/phpunit/compare/12.3.1...12.3.2
[12.3.1]: https://github.com/sebastianbergmann/phpunit/compare/12.3.0...12.3.1
[12.3.0]: https://github.com/sebastianbergmann/phpunit/compare/12.2.9...12.3.0
