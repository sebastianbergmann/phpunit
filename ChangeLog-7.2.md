# Changes in PHPUnit 7.2

All notable changes of the PHPUnit 7.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.2.2] - 2018-06-01

### Changed

* Ensure that `phpunit/php-code-coverage` is used in version `^6.0.7`

## [7.2.1] - 2018-06-01

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

[7.2.2]: https://github.com/sebastianbergmann/phpunit/compare/7.2.1...7.2.2
[7.2.1]: https://github.com/sebastianbergmann/phpunit/compare/7.2.0...7.2.1
[7.2.0]: https://github.com/sebastianbergmann/phpunit/compare/7.1...7.2.0

