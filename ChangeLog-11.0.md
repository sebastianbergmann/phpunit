# Changes in PHPUnit 11.0

All notable changes of the PHPUnit 11.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.0.0] - 2024-02-02

### Changed

* [#5213](https://github.com/sebastianbergmann/phpunit/issues/5213): Make `TestCase` methods `protected` that should have been `protected` all along
* [#5254](https://github.com/sebastianbergmann/phpunit/issues/5254): Make `TestCase` methods `final` that should have been `final` all along

### Removed

* [#4600](https://github.com/sebastianbergmann/phpunit/issues/4600): Support for old cache configuration
* [#4604](https://github.com/sebastianbergmann/phpunit/issues/4604): Support for `backupStaticAttributes` attribute in XML configuration file
* [#4779](https://github.com/sebastianbergmann/phpunit/issues/4779): Support for `forceCoversAnnotation` and `beStrictAboutCoversAnnotation` attributes in XML configuration file
* [#5100](https://github.com/sebastianbergmann/phpunit/issues/5100): Support for non-static data provider methods, non-public data provider methods, and data provider methods that declare parameters
* [#5101](https://github.com/sebastianbergmann/phpunit/issues/5101): PHP 8.1 is no longer supported

[11.0.0]: https://github.com/sebastianbergmann/phpunit/compare/10.5...main
