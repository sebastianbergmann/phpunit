# Changes in PHPUnit 11.3

All notable changes of the PHPUnit 11.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.3.0] - 2024-08-09

### Added

* [#5869](https://github.com/sebastianbergmann/phpunit/pull/5869): `shortenArraysForExportThreshold` attribute on the `<phpunit>` element of the XML configuration file to limit the export of arrays to a specified number of elements (default: `0` / do not limit the export of arrays)

### Changed

* [#5869](https://github.com/sebastianbergmann/phpunit/pull/5869): The configuration file generated using `--generate-configuration` now limits the export of arrays to 10 elements in order to improve performance

[11.3.0]: https://github.com/sebastianbergmann/phpunit/compare/11.2...main
