# Changes in PHPUnit 8.5

All notable changes of the PHPUnit 8.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [8.5.5] - 2020-05-22

### Fixed

* [#4033](https://github.com/sebastianbergmann/phpunit/issues/4033): Unexpected behaviour when `$GLOBALS` is deleted

## [8.5.4] - 2020-04-23

### Changed

* Changed how `PHPUnit\TextUI\Command` passes warnings to `PHPUnit\TextUI\TestRunner`

## [8.5.3] - 2020-03-31

### Fixed

* [#4017](https://github.com/sebastianbergmann/phpunit/issues/4017): Do not suggest refactoring to something that is also deprecated
* [#4133](https://github.com/sebastianbergmann/phpunit/issues/4133): `expectExceptionMessageRegExp()` has been removed in PHPUnit 9 without a deprecation warning being given in PHPUnit 8
* [#4139](https://github.com/sebastianbergmann/phpunit/issues/4139): Cannot double interfaces that declare a constructor with PHP 8
* [#4144](https://github.com/sebastianbergmann/phpunit/issues/4144): Empty objects are converted to empty arrays in JSON comparison failure diff

## [8.5.2] - 2020-01-08

### Removed

* `eval-stdin.php` has been removed, it was not used anymore since PHPUnit 7.2.7

## [8.5.1] - 2019-12-25

### Changed

* `eval-stdin.php` can now only be executed with `cli` and `phpdbg`

### Fixed

* [#3983](https://github.com/sebastianbergmann/phpunit/issues/3983): Deprecation warning given too eagerly

## [8.5.0] - 2019-12-06

### Added

* [#3911](https://github.com/sebastianbergmann/phpunit/issues/3911): Support combined use of `addMethods()` and `onlyMethods()`
* [#3949](https://github.com/sebastianbergmann/phpunit/issues/3949): Introduce specialized assertions `assertFileEqualsCanonicalizing()`, `assertFileEqualsIgnoringCase()`, `assertStringEqualsFileCanonicalizing()`, `assertStringEqualsFileIgnoringCase()`, `assertFileNotEqualsCanonicalizing()`, `assertFileNotEqualsIgnoringCase()`, `assertStringNotEqualsFileCanonicalizing()`, and `assertStringNotEqualsFileIgnoringCase()` as alternative to using `assertFileEquals()` etc. with optional parameters

### Changed

* [#3860](https://github.com/sebastianbergmann/phpunit/pull/3860): Deprecate invoking PHPUnit commandline test runner with just a class name
* [#3950](https://github.com/sebastianbergmann/phpunit/issues/3950): Deprecate optional parameters of `assertFileEquals()` etc.
* [#3955](https://github.com/sebastianbergmann/phpunit/issues/3955): Deprecate support for doubling multiple interfaces

### Fixed

* [#3953](https://github.com/sebastianbergmann/phpunit/issues/3953): Code Coverage for test executed in isolation does not work when the PHAR is used
* [#3967](https://github.com/sebastianbergmann/phpunit/issues/3967): Cannot double interface that extends interface that extends `\Throwable`
* [#3968](https://github.com/sebastianbergmann/phpunit/pull/3968): Test class run in a separate PHP process are passing when `exit` called inside

[8.5.5]: https://github.com/sebastianbergmann/phpunit/compare/8.5.4...8.5.5
[8.5.4]: https://github.com/sebastianbergmann/phpunit/compare/8.5.3...8.5.4
[8.5.3]: https://github.com/sebastianbergmann/phpunit/compare/8.5.2...8.5.3
[8.5.2]: https://github.com/sebastianbergmann/phpunit/compare/8.5.1...8.5.2
[8.5.1]: https://github.com/sebastianbergmann/phpunit/compare/8.5.0...8.5.1
[8.5.0]: https://github.com/sebastianbergmann/phpunit/compare/8.4.3...8.5.0
