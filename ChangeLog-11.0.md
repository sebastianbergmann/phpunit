# Changes in PHPUnit 11.0

All notable changes of the PHPUnit 11.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.0.0] - 2024-02-02

### Changed

* [#5213](https://github.com/sebastianbergmann/phpunit/issues/5213): Make `TestCase` methods `protected` that should have been `protected` all along
* [#5254](https://github.com/sebastianbergmann/phpunit/issues/5254): Make `TestCase` methods `final` that should have been `final` all along

### Deprecated

* [#5214](https://github.com/sebastianbergmann/phpunit/issues/5214): `TestCase::iniSet()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5216](https://github.com/sebastianbergmann/phpunit/issues/5216): `TestCase::setLocale()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): `TestCase::createTestProxy()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): `TestCase::getMockForAbstractClass()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242): `TestCase::getMockFromWsdl()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243): `TestCase::getMockForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244): `TestCase::getObjectForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5305](https://github.com/sebastianbergmann/phpunit/issues/5305): `MockBuilder::getMockForAbstractClass()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5306](https://github.com/sebastianbergmann/phpunit/issues/5306): `MockBuilder::getMockForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): `MockBuilder::enableProxyingToOriginalMethods()`, `MockBuilder::disableProxyingToOriginalMethods()`, and `MockBuilder::setProxyTarget()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308): `MockBuilder::allowMockingUnknownTypes()` and `MockBuilder::disallowMockingUnknownTypes()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309): `MockBuilder::enableAutoload()` and `MockBuilder::disableAutoload()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315): `MockBuilder::enableArgumentCloning()` and `MockBuilder::disableArgumentCloning()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5320](https://github.com/sebastianbergmann/phpunit/issues/5320): `MockBuilder::addMethods()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421): `MockBuilder::enableAutoReturnValueGeneration()` and `MockBuilder::disableAutoReturnValueGeneration()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472): `assertStringNotMatchesFormat()` and `assertStringNotMatchesFormatFile()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)

### Removed

* [#4600](https://github.com/sebastianbergmann/phpunit/issues/4600): Support for old cache configuration
* [#4604](https://github.com/sebastianbergmann/phpunit/issues/4604): Support for `backupStaticAttributes` attribute in XML configuration file
* [#4779](https://github.com/sebastianbergmann/phpunit/issues/4779): Support for `forceCoversAnnotation` and `beStrictAboutCoversAnnotation` attributes in XML configuration file
* [#5100](https://github.com/sebastianbergmann/phpunit/issues/5100): Support for non-static data provider methods, non-public data provider methods, and data provider methods that declare parameters
* [#5101](https://github.com/sebastianbergmann/phpunit/issues/5101): Support for PHP 8.1
* [#5272](https://github.com/sebastianbergmann/phpunit/issues/5272): Optional parameters of `PHPUnit\Framework\Constraint\IsEqual::__construct()`
* [#5329](https://github.com/sebastianbergmann/phpunit/issues/5329): Support for configuring include/exclude list for code coverage using the `<coverage>` element
* [#5482](https://github.com/sebastianbergmann/phpunit/issues/5482): `dataSet` attribute for `testCaseMethod` elements in the XML document generated by `--list-tests-xml`
* [#5514](https://github.com/sebastianbergmann/phpunit/issues/5514:) `IgnoreClassForCodeCoverage`, `IgnoreMethodForCodeCoverage`, and `IgnoreFunctionForCodeCoverage` attributes
* `CodeCoverageIgnore` attribute

[11.0.0]: https://github.com/sebastianbergmann/phpunit/compare/10.5...main
