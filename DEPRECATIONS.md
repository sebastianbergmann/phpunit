# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

* [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472): `TestCase::assertStringNotMatchesFormat()` and `TestCase::assertStringNotMatchesFormatFile()` (since PHPUnit 10.4.0)

#### Test Double API

* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): `TestCase::createTestProxy()` (since PHPUnit 10.1.0)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): `TestCase::getMockForAbstractClass()` (since PHPUnit 10.1.0)
* [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242): `TestCase::getMockFromWsdl()` (since PHPUnit 10.1.0)
* [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243): `TestCase::getMockForTrait()` (since PHPUnit 10.1.0)
* [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244): `TestCase::getObjectForTrait()` (since PHPUnit 10.1.0)
* [#5305](https://github.com/sebastianbergmann/phpunit/issues/5305): `MockBuilder::getMockForAbstractClass()` (since PHPUnit 10.1.0)
* [#5306](https://github.com/sebastianbergmann/phpunit/issues/5306): `MockBuilder::getMockForTrait()` (since PHPUnit 10.1.0)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): `MockBuilder::disableProxyingToOriginalMethods()` (since PHPUnit 10.1.0)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): `MockBuilder::enableProxyingToOriginalMethods()` (since PHPUnit 10.1.0)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): `MockBuilder::setProxyTarget()` (since PHPUnit 10.1.0)
* [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308): `MockBuilder::allowMockingUnknownTypes()` (since PHPUnit 10.1.0)
* [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308): `MockBuilder::disallowMockingUnknownTypes()` (since PHPUnit 10.1.0)
* [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309): `MockBuilder::disableAutoload()` (since PHPUnit 10.1.0)
* [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309): `MockBuilder::enableAutoload()` (since PHPUnit 10.1.0)
* [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315): `MockBuilder::disableArgumentCloning()` (since PHPUnit 10.1.0)
* [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315): `MockBuilder::enableArgumentCloning()` (since PHPUnit 10.1.0)
* [#5320](https://github.com/sebastianbergmann/phpunit/issues/5320): `MockBuilder::addMethods()` (since PHPUnit 10.1.0)
* [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421): `MockBuilder::disableAutoReturnValueGeneration()` (since PHPUnit 10.3.0)
* [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421): `MockBuilder::enableAutoReturnValueGeneration()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::onConsecutiveCalls()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnArgument()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnCallback()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnSelf()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnValue()` (since PHPUnit 10.3.0)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnValueMap()` (since PHPUnit 10.3.0)

#### Miscellaneous

* [#5214](https://github.com/sebastianbergmann/phpunit/issues/5214): `TestCase::iniSet()` (since PHPUnit 10.3.0)
* [#5216](https://github.com/sebastianbergmann/phpunit/issues/5216): `TestCase::setLocale()` (since PHPUnit 10.3.0)
* [#5236](https://github.com/sebastianbergmann/phpunit/issues/5236): `PHPUnit\Framework\Attributes\CodeCoverageIgnore()` (since PHPUnit 10.1.0)

### Extending PHPUnit

* `PHPUnit\TextUI\Configuration\Configuration::excludeDirectories()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::excludeFiles()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::includeDirectories()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::includeFiles()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::noExtensions()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::notEmpty()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::restrictDeprecations()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::restrictNotices()` (since PHPUnit 10.2.0)
* `PHPUnit\TextUI\Configuration\Configuration::restrictWarnings()` (since PHPUnit 10.2.0)

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Miscellaneous

* [#5100](https://github.com/sebastianbergmann/phpunit/issues/5100): Support for non-static data provider methods, non-public data provider methods, and data provider methods that declare parameters (since PHPUnit 10.0.0)
