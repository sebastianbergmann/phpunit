# Changes in PHPUnit 13.3

All notable changes of the PHPUnit 13.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.3.0] - 2026-08-07

### Added

* [#6586](https://github.com/sebastianbergmann/phpunit/pull/6586): Custom code coverage driver support
* [#6591](https://github.com/sebastianbergmann/phpunit/pull/6591): Repeated test execution using `--repeat` CLI option and `#[Repeat]` attribute
* [#6701](https://github.com/sebastianbergmann/phpunit/pull/6701): Allow `expectOutputString()` and `expectOutputRegex()` to be combined and repeated
* [#6710](https://github.com/sebastianbergmann/phpunit/pull/6710): Deprecation Filters
* [#6722](https://github.com/sebastianbergmann/phpunit/issues/6722): Allow `#[CoversNothing]` on methods
* [#6742](https://github.com/sebastianbergmann/phpunit/pull/6742): Retry failing tests up to N times using `--retry` CLI option `#[Retry]` attribute
* `--without-class-view` CLI option and `classView` attribute for the XML configuration file to disable the [class-oriented view](https://github.com/sebastianbergmann/php-code-coverage/pull/1140) in the HTML code coverage report

### Changed

* The test runner no longer crashes when an attribute cannot be instantiated
* Improved TestDox HTML report

[13.3.0]: https://github.com/sebastianbergmann/phpunit/compare/13.2...main
