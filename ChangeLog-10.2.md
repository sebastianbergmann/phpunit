# Changes in PHPUnit 10.2

All notable changes of the PHPUnit 10.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.2.1] - 2023-06-05

### Changed

* `PHPUnit\Runner\ErrorHandler` no longer emits events for errors that occur in PHPUnit's own code (or code of its dependencies) and are suppressed using the `@` operator

## [10.2.0] - 2023-06-02

### Added

* [#5328](https://github.com/sebastianbergmann/phpunit/issues/5328): Optionally ignore suppression of deprecations, notices, and warnings
* `PHPUnit\Event\Test\DataProviderMethodCalled` and `PHPUnit\Event\Test\DataProviderMethodFinished` events

### Changed

* Improved the reporting of errors during the loading and bootstrapping of test runner extensions

### Deprecated

* `PHPUnit\TextUI\Configuration\Configuration::restrictDeprecations()` (use `source()->restrictDeprecations()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictNotices()` (use `source()->restrictNotices()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictWarnings()` (use `source()->restrictWarnings()` instead)

### Fixed

* [#5364](https://github.com/sebastianbergmann/phpunit/issues/5364): Confusing warning message `Class ... cannot be found` when class is found, but does not extend `PHPUnit\Framework\TestCase`
* [#5366](https://github.com/sebastianbergmann/phpunit/issues/5366): `PHPUnit\Event\TestSuite\Loaded` event has incomplete `PHPUnit\Event\TestSuite\TestSuite` value object
* Always use `X.Y.Z` version number (and not just `X.Y`) of PHPUnit's version when checking whether a PHAR-distributed extension is compatible

[10.2.1]: https://github.com/sebastianbergmann/phpunit/compare/10.2.0...10.2.1
[10.2.0]: https://github.com/sebastianbergmann/phpunit/compare/10.1.3...10.2.0
