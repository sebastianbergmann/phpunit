# Changes in PHPUnit 5.7

All notable changes of the PHPUnit 5.7 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.7.0] - 2016-12-02

### Added

* Merged [#2223](https://github.com/sebastianbergmann/phpunit/pull/2223): Add support for multiple data providers
* Added `extensionsDirectory` configuration directive to configure a directory from which all `.phar` files are loaded as PHPUnit extensions
* Added `--no-extensions` commandline option to suppress loading of extensions (from configured extension directory)
* Added `PHPUnit\Framework\Assert` as an alias for `PHPUnit_Framework_Assert` for forward compatibility
* Added `PHPUnit\Framework\BaseTestListener` as an alias for `PHPUnit_Framework_BaseTestListener` for forward compatibility
* Added `PHPUnit\Framework\TestListener` as an alias for `PHPUnit_Framework_TestListener` for forward compatibility

### Changed

* The `--log-json` commandline option has been deprecated
* The `--tap` and `--log-tap` commandline options have been deprecated
* The `--self-update` and `--self-upgrade` commandline options have been deprecated (PHAR binary only)

[5.7.0]: https://github.com/sebastianbergmann/phpunit/compare/5.6...5.7.0

