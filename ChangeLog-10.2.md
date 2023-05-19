# Changes in PHPUnit 10.2

All notable changes of the PHPUnit 10.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.2.0] - 2023-06-02

### Added

* [#5328](https://github.com/sebastianbergmann/phpunit/issues/5328): Optionally ignore suppression of deprecations, notices, and warnings
* `PHPUnit\Event\Test\DataProviderMethodCalled` and `PHPUnit\Event\Test\DataProviderMethodFinished` events

### Deprecated

* `PHPUnit\TextUI\Configuration\Configuration::restrictDeprecations()` (use `source()->restrictDeprecations()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictNotices()` (use `source()->restrictNotices()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictWarnings()` (use `source()->restrictWarnings()` instead)

[10.2.0]: https://github.com/sebastianbergmann/phpunit/compare/10.1...main
