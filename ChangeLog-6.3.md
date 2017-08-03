# Changes in PHPUnit 6.3

All notable changes of the PHPUnit 6.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.3.0] - 2017-08-04

### Added

* Implemented [#2722](https://github.com/sebastianbergmann/phpunit/pull/2722): `@requires OSFAMILY` annotation
* Implemented [#2723](https://github.com/sebastianbergmann/phpunit/pull/2723): Provide a way to force set an environment variable from XML configuration file

### Changed

* Implemented [#2751](https://github.com/sebastianbergmann/phpunit/pull/2751): Use `fopen()` instead of `is_readable()` to check if a file is readable (workaround for Windows and network shares) 

[6.3.0]: https://github.com/sebastianbergmann/phpunit/compare/6.2...6.3.0

