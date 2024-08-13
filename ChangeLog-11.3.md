# Changes in PHPUnit 11.3

All notable changes of the PHPUnit 11.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.3.1] - 2024-08-13

### Changed

* Improved how objects are handled for some assertion failure messages

## [11.3.0] - 2024-08-09

### Added

* [#5869](https://github.com/sebastianbergmann/phpunit/pull/5869): `shortenArraysForExportThreshold` attribute on the `<phpunit>` element of the XML configuration file to limit the export of arrays to a specified number of elements (default: `0` / do not limit the export of arrays)
* [#5885](https://github.com/sebastianbergmann/phpunit/pull/5885): Optionally repeat TestDox output for non-successful tests after the regular TestDox output
* [#5890](https://github.com/sebastianbergmann/phpunit/pull/5890): Priority for hook methods
* [#5906](https://github.com/sebastianbergmann/phpunit/issues/5906): `--extension` CLI option to register a test runner extension

### Changed

* [#5856](https://github.com/sebastianbergmann/phpunit/issues/5856): When the test runner is configured to fail on deprecations, notices, warnings, incomplete tests, or skipped tests then details for tests that triggered deprecations, notices, or warnings as well as tests that were marked as incomplete or skipped are always shown, respectively
* [#5869](https://github.com/sebastianbergmann/phpunit/pull/5869): The configuration file generated using `--generate-configuration` now limits the export of arrays to 10 elements in order to improve performance

[11.3.1]: https://github.com/sebastianbergmann/phpunit/compare/11.3.0...11.3.1
[11.3.0]: https://github.com/sebastianbergmann/phpunit/compare/11.2.9...11.3.0
