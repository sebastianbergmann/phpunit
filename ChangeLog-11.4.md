# Changes in PHPUnit 11.4

All notable changes of the PHPUnit 11.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.4.2] - 2024-10-19

### Changed

* [#5989](https://github.com/sebastianbergmann/phpunit/pull/5989): Disable Xdebug in subprocesses when it is not used

### Fixed

* [#5844](https://github.com/sebastianbergmann/phpunit/issues/5844): Error handlers that are not callable outside the scope they were registered in are not handled correctly
* [#5982](https://github.com/sebastianbergmann/phpunit/pull/5982): Typo in exception message

## [11.4.1] - 2024-10-08

### Changed

* Updated regular expressions used by `StringMatchesFormatDescription` constraint to be consistent with PHP's `run-tests.php`

### Fixed

* [#5977](https://github.com/sebastianbergmann/phpunit/pull/5977): TestDox result collector does not correctly handle baseline-ignored `E_DEPRECATED` issues

## [11.4.0] - 2024-10-05

### Changed

* [#5915](https://github.com/sebastianbergmann/phpunit/pull/5915): Bootstrap extensions before building test suite
* [#5917](https://github.com/sebastianbergmann/phpunit/pull/5917): Seal event facade before loading the test suite
* [#5923](https://github.com/sebastianbergmann/phpunit/pull/5923): Filter configured deprecation triggers when displaying deprecation details
* [#5927](https://github.com/sebastianbergmann/phpunit/pull/5927): `#[RequiresPhpunitExtension]` attribute
* [#5928](https://github.com/sebastianbergmann/phpunit/issues/5928): Filter tests based on the PHP extensions they require
* [#5964](https://github.com/sebastianbergmann/phpunit/pull/5964): Better error message when data provider is invalid
* The XML configuration file generator now references `vendor/phpunit/phpunit/phpunit.xsd` (instead of `https://schema.phpunit.de/X.Y/phpunit.xsd`) when PHPUnit was installed using Composer and `phpunit --generate-configuration` was invoked in the directory where `vendor` is located
* The `--migrate-configuration` command no longer replaces `vendor/phpunit/phpunit/phpunit.xsd` with `https://schema.phpunit.de/X.Y/phpunit.xsd`
* The output of `--list-groups` now shows how many tests a group contains
* The output of `--list-suites` now shows how many tests a test suite contains

### Deprecated

* [#5951](https://github.com/sebastianbergmann/phpunit/issues/5951): `includeUncoveredFiles` configuration option
* [#5958](https://github.com/sebastianbergmann/phpunit/issues/5958): Support for `#[CoversTrait]` and `#[UsesTrait]` attributes
* [#5960](https://github.com/sebastianbergmann/phpunit/issues/5960): Support for targeting trait methods with the `#[CoversMethod]` and `#[UsesMethod]` attributes (and respective annotations)

[11.4.2]: https://github.com/sebastianbergmann/phpunit/compare/11.4.1...11.4.2
[11.4.1]: https://github.com/sebastianbergmann/phpunit/compare/11.4.0...11.4.1
[11.4.0]: https://github.com/sebastianbergmann/phpunit/compare/11.3.6...11.4.0
