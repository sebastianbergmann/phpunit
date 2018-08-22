# Changes in PHPUnit 7.3

All notable changes of the PHPUnit 7.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.3.2] - 2018-08-22

### Fixed

* Fixed [#3219](https://github.com/sebastianbergmann/phpunit/issues/3219): `getMockFromWsdl()` generates invalid PHP code when WSDL filename contains special characters
* Fixed [#3248](https://github.com/sebastianbergmann/phpunit/issues/3248) and [#3233](https://github.com/sebastianbergmann/phpunit/issues/3233): `phpunit.xsd` dictates element order where it should not
* Fixed [#3251](https://github.com/sebastianbergmann/phpunit/issues/3251): TeamCity result logger is missing test duration information

## [7.3.1] - 2018-08-07

### Changed

* Reverted [#3161](https://github.com/sebastianbergmann/phpunit/pull/3161) (because of [#3240](https://github.com/sebastianbergmann/phpunit/issues/3240)): Support for indexed arrays in `PHPUnit\Framework\Constraint\ArraySubset`

### Fixed

* Fixed [#3237](https://github.com/sebastianbergmann/phpunit/issues/3237): Result caching enabled by default
* Fixed [#3240](https://github.com/sebastianbergmann/phpunit/issues/3240): `assertArraySubset()` does not work as expected

## [7.3.0] - 2018-08-03

### Added

* Implemented [#3147](https://github.com/sebastianbergmann/phpunit/pull/3147): Support for running tests first that failed in a previous run
  * Implemented `cacheResult` configuration directive and `--cache-result` CLI option to control test result cache required for "run defects first" functionality (disabled by default)
  * Implemented `cacheResultFile` configuration directive and `--cache-result-file` CLI option to configure test result cache file (default: `.phpunit.result.cache`)
  * Implemented `stopOnDefect` configuration directive and `--stop-on-defect` CLI option for aborting test suite execution upon first defective test
  * Implemented `executionOrder` configuration directive and `--order-by` CLI option for sorting the test suite before execution
  * The `--order-by=random` CLI option should now be used instead of `--random-order`
  * The `--order-by=depends` CLI option should now be used instead of `--resolve-dependencies`
  * The `--order-by=reverse` CLI option should now be used instead of `--reverse-order`
* Implemented [#3161](https://github.com/sebastianbergmann/phpunit/pull/3161): Support for indexed arrays in `PHPUnit\Framework\Constraint\ArraySubset`
* Implemented [#3194](https://github.com/sebastianbergmann/phpunit/issues/3194): `@covers class` (and `@uses class`) should include traits used by class
* Implemented [#3196](https://github.com/sebastianbergmann/phpunit/issues/3196): Support for replacing placeholders in `@testdox` text with data provider values
* Implemented [#3198](https://github.com/sebastianbergmann/phpunit/pull/3198): Provide source location for useless tests

### Fixed

* Fixed [#3154](https://github.com/sebastianbergmann/phpunit/issues/3154): Global constants as default parameter values are not handled correctly in namespace
* Fixed [#3218](https://github.com/sebastianbergmann/phpunit/issues/3218): `prefix` attribute for `directory` node missing from `phpunit.xml` XSD
* Fixed [#3222](https://github.com/sebastianbergmann/phpunit/pull/3222): Priority of `@covers` and `@coversNothing` is wrong
* Fixed [#3225](https://github.com/sebastianbergmann/phpunit/issues/3225): `coverage-php` missing from `phpunit.xsd`

[7.3.2]: https://github.com/sebastianbergmann/phpunit/compare/7.3.1...7.3.2
[7.3.1]: https://github.com/sebastianbergmann/phpunit/compare/7.3.0...7.3.1
[7.3.0]: https://github.com/sebastianbergmann/phpunit/compare/7.2...7.3.0

