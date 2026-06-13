# Changes in PHPUnit 13.3

All notable changes of the PHPUnit 13.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.3.0] - 2026-08-07

### Added

* [#6701](https://github.com/sebastianbergmann/phpunit/pull/6701): Allow `expectOutputString()` and `expectOutputRegex()` to be combined and repeated
* [#6710](https://github.com/sebastianbergmann/phpunit/pull/6710): Deprecation Filters
* [#6722](https://github.com/sebastianbergmann/phpunit/issues/6722): Allow `#[CoversNothing]` on methods

### Changed

* The test runner no longer crashes when an attribute cannot be instantiated
* Improved TestDox HTML report

[13.3.0]: https://github.com/sebastianbergmann/phpunit/compare/13.2...main
