# Changes in PHPUnit 7.2

All notable changes of the PHPUnit 7.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.2.7] - 2018-07-15

### Fixed

* Fixed [#3154](https://github.com/sebastianbergmann/phpunit/issues/3154): Global constants as default parameter values are not handled correctly in namespace
* Fixed [#3189](https://github.com/sebastianbergmann/phpunit/issues/3189): PHPUnit 7.2 potentially leaves a messy libxmlerror state
* Fixed [#3199](https://github.com/sebastianbergmann/phpunit/pull/3199): Code Coverage for PHPT tests does not work when PHPDBG is used

## [7.2.6] - 2018-06-21

### Fixed

* Fixed [#3176](https://github.com/sebastianbergmann/phpunit/issues/3176): PHPUnit 7.2.5 breaks backward compatibility

## [7.2.5] - 2018-06-21

### Fixed

* Fixed [#3093](https://github.com/sebastianbergmann/phpunit/issues/3093): Unable to chain a `@dataProvider` in method `a` with a `@depends` in method `b`
* Fixed [#3174](https://github.com/sebastianbergmann/phpunit/issues/3174): Code generator for test doubles does not handle proxied methods with variadic parameters correctly

## [7.2.4] - 2018-06-05

### Fixed

* Fixed [#3160](https://github.com/sebastianbergmann/phpunit/issues/3160): TeamCity logfile writer broken on Windows

## [7.2.3] - 2018-06-03

### Fixed

* Fixed [#3156](https://github.com/sebastianbergmann/phpunit/issues/3156): Combined use of `@depends` and `@dataProvider` is not handled correctly

## [7.2.2] - 2018-06-01

### Changed

* Ensure that `phpunit/php-code-coverage` is used in version `^6.0.7`

## [7.2.1] - 2018-06-01

### Fixed

* Fixed [#3155](https://github.com/sebastianbergmann/phpunit/issues/3155): Calling `getStatus()` on a `TestCase` object before the respective test has been executed results in type error

## [7.2.0] - 2018-06-01

### Added

* Implemented [#3042](https://github.com/sebastianbergmann/phpunit/pull/3042): Add `TestCase::expectNotToPerformAssertions()` method as alternative to `@doesNotPerformAssertions` annotation
* Implemented [#3064](https://github.com/sebastianbergmann/phpunit/issues/3064): Mark tests as risky when they claim not to perform assertions but do
* Implemented [#3066](https://github.com/sebastianbergmann/phpunit/issues/3066): Validate XML configuration against XSD
* Implemented [#3076](https://github.com/sebastianbergmann/phpunit/issues/3076): Extensions can be configured via PHPUnit's XML configuration
* Implemented [#3080](https://github.com/sebastianbergmann/phpunit/issues/3080): The XML configuration arguments can have boolean elements
* Implemented [#3092](https://github.com/sebastianbergmann/phpunit/pull/3092): Ability to run tests in random order, reverse order, ordered using dependency resolution

### Changed

* Implemented [#3103](https://github.com/sebastianbergmann/phpunit/issues/3103): Merge `phpunit-mock-objects` back into PHPUnit's Git repository
* Implemented [#3115](https://github.com/sebastianbergmann/phpunit/pull/3115): Method-level `@covers` annotation overrides class-level `@coversNothing` annotation

### Removed

* Fixed [#3069](https://github.com/sebastianbergmann/phpunit/issues/3069): Method `ResultPrinter::printWaitPrompt()` seems to be unused

[7.2.7]: https://github.com/sebastianbergmann/phpunit/compare/7.2.6...7.2.7
[7.2.6]: https://github.com/sebastianbergmann/phpunit/compare/7.2.5...7.2.6
[7.2.5]: https://github.com/sebastianbergmann/phpunit/compare/7.2.4...7.2.5
[7.2.4]: https://github.com/sebastianbergmann/phpunit/compare/7.2.3...7.2.4
[7.2.3]: https://github.com/sebastianbergmann/phpunit/compare/7.2.2...7.2.3
[7.2.2]: https://github.com/sebastianbergmann/phpunit/compare/7.2.1...7.2.2
[7.2.1]: https://github.com/sebastianbergmann/phpunit/compare/7.2.0...7.2.1
[7.2.0]: https://github.com/sebastianbergmann/phpunit/compare/7.1...7.2.0

