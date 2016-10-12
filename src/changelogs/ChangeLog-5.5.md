# Changes in PHPUnit 5.5

All notable changes of the PHPUnit 5.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.5.7] - 2016-10-03

### Changed

* Reverted [#2300](https://github.com/sebastianbergmann/phpunit/issues/2300): Exclude tests from package distribution

## [5.5.6] - 2016-10-03

### Changed

* Implemented [#2300](https://github.com/sebastianbergmann/phpunit/issues/2300): Exclude tests from package distribution

### Fixed

* Fixed [#2261](https://github.com/sebastianbergmann/phpunit/issues/2261): Invalid test listener configuration leads to confusing behavior
* Fixed [#2309](https://github.com/sebastianbergmann/phpunit/pull/2309): `PHPUnit\Framework\TestCase` is not declared `abstract`

## [5.5.5] - 2016-09-21

### Fixed

* Fixed [#2101](https://github.com/sebastianbergmann/phpunit/issues/2101): Output Buffer Level consumption prevents custom output buffers from working

## [5.5.4] - 2016-08-26

New release of PHPUnit as PHAR with updated dependencies

## [5.5.3] - 2016-08-25

### Fixed

* Fixed [#2270](https://github.com/sebastianbergmann/phpunit/pull/2270): Allow `createPartialMock()` to not mock any methods

## [5.5.2] - 2016-08-18

### Changed

* The JUnit logger no longer uses `<warning>` elements when the `logIncompleteSkipped` configuration option is set to `false` (default)

### Fixed

* Restored the `logIncompleteSkipped` configuration option for the JUnit logger that got lost in PHPUnit 5.4.2

## [5.5.1] - 2016-08-17

### Fixed

* Fixed [#1961](https://github.com/sebastianbergmann/phpunit/issues/1961): XSD schema in 5.x does not validate
* Incorrect warning about missing `@covers` annotation is no longer shown when `@coversNothing` is used together with `forceCoversAnnotation=true`

## [5.5.0] - 2016-08-05

### Added

* Added the `PHPUnit\Framework\TestCase::createPartialMock()` method for creating partial test doubles using best practice defaults
* Merged [#2203](https://github.com/sebastianbergmann/phpunit/pull/2203): Ability to `--list-suites` for a given configuration

### Changed

* An `AssertionError` raised by an `assert()` in the tested code now causes the test to be interpreted as a failure instead of an error

[5.5.7]: https://github.com/sebastianbergmann/phpunit/compare/5.5.6...5.5.7
[5.5.6]: https://github.com/sebastianbergmann/phpunit/compare/5.5.5...5.5.6
[5.5.5]: https://github.com/sebastianbergmann/phpunit/compare/5.5.4...5.5.5
[5.5.4]: https://github.com/sebastianbergmann/phpunit/compare/5.5.3...5.5.4
[5.5.3]: https://github.com/sebastianbergmann/phpunit/compare/5.5.2...5.5.3
[5.5.2]: https://github.com/sebastianbergmann/phpunit/compare/5.5.1...5.5.2
[5.5.1]: https://github.com/sebastianbergmann/phpunit/compare/5.5.0...5.5.1
[5.5.0]: https://github.com/sebastianbergmann/phpunit/compare/5.4...5.5.0

