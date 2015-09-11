# Changes in PHPUnit 4.3

All notable changes of the PHPUnit 4.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.3.5] - 2014-11-11

### Changed

* Merged [#1484](https://github.com/sebastianbergmann/phpunit/issues/1484): Removed `lazymap` from blacklist as it is not longer used
* Merged [#1489](https://github.com/sebastianbergmann/phpunit/issues/1489): Do not send output from tests in process isolation when testing output

## [4.3.4] - 2014-10-22

### Fixed

* Fixed [#1428](https://github.com/sebastianbergmann/phpunit/issues/1428): Issue with Composer dependencies
* Fixed [#1447](https://github.com/sebastianbergmann/phpunit/issues/1447): PHPT tests treat line endings inconsistently

## [4.3.3] - 2014-10-16

### Fixed

* Fixed [#1471](https://github.com/sebastianbergmann/phpunit/issues/1471): Output made while test is running is printed although `expectOutputString()` is used when an assertion fails

## [4.3.2] - 2014-10-16

### Fixed

* Fixed [#1468](https://github.com/sebastianbergmann/phpunit/issues/1468): Incomplete and `@todo` annotated tests are counted twice

## [4.3.1] - 2014-10-06

New release of PHPUnit as PHP Archive (PHAR) with updated dependencies

## [4.3.0] - 2014-10-03

### Added

* Merged [#1358](https://github.com/sebastianbergmann/phpunit/issues/1358): Implement `@expectedExceptionMessageRegExp` annotation
* Merged [#1360](https://github.com/sebastianbergmann/phpunit/issues/1360): Allow a test to identify whether it runs in isolation

### Fixed

* Fixed [#1216](https://github.com/sebastianbergmann/phpunit/issues/1216): Bootstrap does not have global variables set when `--bootstrap` is specified on commandline
* Fixed [#1351](https://github.com/sebastianbergmann/phpunit/issues/1351): `TestResult` object contains serialized test class upon test failure/exception in process isolation
* Fixed [#1437](https://github.com/sebastianbergmann/phpunit/issues/1437): Risky test messages mask failures 

[4.3.5]: https://github.com/sebastianbergmann/phpunit/compare/4.3.4...4.3.5
[4.3.4]: https://github.com/sebastianbergmann/phpunit/compare/4.3.3...4.3.4
[4.3.3]: https://github.com/sebastianbergmann/phpunit/compare/4.3.2...4.3.3
[4.3.2]: https://github.com/sebastianbergmann/phpunit/compare/4.3.1...4.3.2
[4.3.1]: https://github.com/sebastianbergmann/phpunit/compare/4.3.0...4.3.1
[4.3.0]: https://github.com/sebastianbergmann/phpunit/compare/4.2...4.3.0

