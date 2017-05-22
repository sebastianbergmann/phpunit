# Changes in PHPUnit 6.1

All notable changes of the PHPUnit 6.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.1.4] - 2017-05-22

### Changed

* Version 3.0.2 of `sebastian/environment` is now required

### Fixed

* Fixed [#2563](https://github.com/sebastianbergmann/phpunit/pull/2563): `phpunit --version` does not display version when running unsupported PHP

## [6.1.3] - 2017-04-29

* Fixed [#2661](https://github.com/sebastianbergmann/phpunit/pull/2661): Inconsistent information in JUnit XML logfile for tests that use data provider

## [6.1.2] - 2017-04-25

### Changed

* Version 3.0.1 of `sebastian/environment` is now required

## [6.1.1] - 2017-04-21

### Changed

* Version 5.2 of `phpunit/php-code-coverage` is now required
* Version 3.0.0 of `sebastian/environment` is now required

## [6.1.0] - 2017-04-07

### Added

* Implemented [#2437](https://github.com/sebastianbergmann/phpunit/issues/2437): Show previous exceptions in TeamCity logfile
* Implemented [#2533](https://github.com/sebastianbergmann/phpunit/pull/2533): Implement configuration option to set a default test suite
* Implemented [#2541](https://github.com/sebastianbergmann/phpunit/issues/2541): Implement configuration option to ignore deprecated code from code coverage
* Implemented [#2546](https://github.com/sebastianbergmann/phpunit/issues/2546): Render `__FILE__` and `__DIR__` in `SKIPIF` section of PHPT tests
* Implemented [#2551](https://github.com/sebastianbergmann/phpunit/issues/2551): Allow directory traversal in `FILE_EXTERNAL` section of PHPT tests
* Implemented [#2579](https://github.com/sebastianbergmann/phpunit/issues/2579): Added `classname` attribute to JUnit XML logfile
* Implemented [#2623](https://github.com/sebastianbergmann/phpunit/pull/2623): Added support for Composer-style version constraints to `@requires` annotation

### Changed

* `.phar` files that are to be loaded as a PHPUnit extension must now have a valid `manifest.xml` file
* Details about risky tests are now always displayed

### Fixed

* Fixed [#2049](https://github.com/sebastianbergmann/phpunit/issues/2049): Misleading error message when whitelist configuration contains paths that do not exist
* Fixed [#2472](https://github.com/sebastianbergmann/phpunit/issues/2472): `PHPUnit\Util\Getopt` uses deprecated `each()` function

[6.1.4]: https://github.com/sebastianbergmann/phpunit/compare/6.1.3...6.1.4
[6.1.3]: https://github.com/sebastianbergmann/phpunit/compare/6.1.2...6.1.3
[6.1.2]: https://github.com/sebastianbergmann/phpunit/compare/6.1.1...6.1.2
[6.1.1]: https://github.com/sebastianbergmann/phpunit/compare/6.1.0...6.1.1
[6.1.0]: https://github.com/sebastianbergmann/phpunit/compare/6.0...6.1.0

