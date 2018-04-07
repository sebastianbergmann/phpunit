# Changes in PHPUnit 7.2

All notable changes of the PHPUnit 7.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.2.0] - 2018-06-01

### Added

* Implemented [#3064](https://github.com/sebastianbergmann/phpunit/issues/3064): Mark tests as risky when they claim not to perform assertions but do
* Implemented [#3066](https://github.com/sebastianbergmann/phpunit/issues/3066): Validate XML configuration against XSD
* Implemented [#3076](https://github.com/sebastianbergmann/phpunit/issues/3076): Extensions can be configured via PHPUnit's XML configuration
* Implemented [#3080](https://github.com/sebastianbergmann/phpunit/issues/3080): The XML configuration arguments can have boolean elements
* Implemented [#3076](https://github.com/sebastianbergmann/phpunit/issues/3076): Extensions can be configured via PHPUnit's XML configuration

### Removed

* Fixed [#3069](https://github.com/sebastianbergmann/phpunit/issues/3069): Method `ResultPrinter::printWaitPrompt()` seems to be unused

[7.2.0]: https://github.com/sebastianbergmann/phpunit/compare/7.1...7.2.0

