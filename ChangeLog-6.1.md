# Changes in PHPUnit 6.1

All notable changes of the PHPUnit 6.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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

[6.1.0]: https://github.com/sebastianbergmann/phpunit/compare/6.0...6.1.0

