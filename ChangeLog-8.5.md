# Changes in PHPUnit 8.5

All notable changes of the PHPUnit 8.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [8.5.3] - 2020-MM-DD

### Fixed

* Fixed [#4017](https://github.com/sebastianbergmann/phpunit/issues/4017): Do not suggest refactoring to something that is also deprecated

## [8.5.2] - 2020-01-08

### Removed

* `eval-stdin.php` has been removed, it was not used anymore since PHPUnit 7.2.7

## [8.5.1] - 2019-12-25

### Changed

* `eval-stdin.php` can now only be executed with `cli` and `phpdbg`

### Fixed

* Fixed [#3983](https://github.com/sebastianbergmann/phpunit/issues/3983): Deprecation warning given too eagerly

## [8.5.0] - 2019-12-06

### Added

* Implemented [#3911](https://github.com/sebastianbergmann/phpunit/issues/3911): Support combined use of `addMethods()` and `onlyMethods()`
* Implemented [#3949](https://github.com/sebastianbergmann/phpunit/issues/3949): Introduce specialized assertions `assertFileEqualsCanonicalizing()`, `assertFileEqualsIgnoringCase()`, `assertStringEqualsFileCanonicalizing()`, `assertStringEqualsFileIgnoringCase()`, `assertFileNotEqualsCanonicalizing()`, `assertFileNotEqualsIgnoringCase()`, `assertStringNotEqualsFileCanonicalizing()`, and `assertStringNotEqualsFileIgnoringCase()` as alternative to using `assertFileEquals()` etc. with optional parameters

### Changed

* Implemented [#3860](https://github.com/sebastianbergmann/phpunit/pull/3860): Deprecate invoking PHPUnit commandline test runner with just a class name
* Implemented [#3950](https://github.com/sebastianbergmann/phpunit/issues/3950): Deprecate optional parameters of `assertFileEquals()` etc.
* Implemented [#3955](https://github.com/sebastianbergmann/phpunit/issues/3955): Deprecate support for doubling multiple interfaces

### Fixed

* Fixed [#3953](https://github.com/sebastianbergmann/phpunit/issues/3953): Code Coverage for test executed in isolation does not work when the PHAR is used
* Fixed [#3967](https://github.com/sebastianbergmann/phpunit/issues/3967): Cannot double interface that extends interface that extends `\Throwable`
* Fixed [#3968](https://github.com/sebastianbergmann/phpunit/pull/3968): Test class run in a separate PHP process are passing when `exit` called inside

[8.5.3]: https://github.com/sebastianbergmann/phpunit/compare/8.5.2...8.5.3
[8.5.2]: https://github.com/sebastianbergmann/phpunit/compare/8.5.1...8.5.2
[8.5.1]: https://github.com/sebastianbergmann/phpunit/compare/8.5.0...8.5.1
[8.5.0]: https://github.com/sebastianbergmann/phpunit/compare/8.4.3...8.5.0
