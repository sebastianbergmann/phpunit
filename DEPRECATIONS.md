# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Extending PHPUnit

| Issue | Description                                                                                            | Since  | Replacement                                                                    |
|-------|--------------------------------------------------------------------------------------------------------|--------|--------------------------------------------------------------------------------|
|       | `PHPUnit\TextUI\Configuration\Configuration::excludeDirectories()`                                     | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->excludeDirectories()`   |
|       | `PHPUnit\TextUI\Configuration\Configuration::excludeFiles()`                                           | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->excludeFiles()`         |
|       | `PHPUnit\TextUI\Configuration\Configuration::includeDirectories()`                                     | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->includeDirectories()`   |
|       | `PHPUnit\TextUI\Configuration\Configuration::includeFiles()`                                           | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->includeFiles()`         |
|       | `PHPUnit\TextUI\Configuration\Configuration::loadPharExtensions()`                                     | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::noExtensions()`                   |
|       | `PHPUnit\TextUI\Configuration\Configuration::hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()` | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->notEmpty()`             |
|       | `PHPUnit\TextUI\Configuration\Configuration::restrictDeprecations()`                                   | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->restrictDeprecations()` |
|       | `PHPUnit\TextUI\Configuration\Configuration::restrictNotices()`                                        | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->restrictNotices()`      |
|       | `PHPUnit\TextUI\Configuration\Configuration::restrictWarnings()`                                       | 10.2.0 | `PHPUnit\TextUI\Configuration\Configuration::source()->restrictWarnings()`     |
|       | `PHPUnit\TextUI\Configuration\Configuration::cliArgument()`                                            | 10.4.0 | `PHPUnit\TextUI\Configuration\Configuration::cliArguments()[0]`                |
|       | `PHPUnit\TextUI\Configuration\Configuration::hasCliArgument()`                                         | 10.4.0 | `PHPUnit\TextUI\Configuration\Configuration::hasCliArguments()`                |
|       | `PHPUnit\Framework\Constraint\Constraint::exporter()`                                                  | 10.4.0 |                                                                                |

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                    | Since  | Replacement |
|-------------------------------------------------------------------|------------------------------------------------|--------|-------------|
| [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472) | `TestCase::assertStringNotMatchesFormat()`     | 10.4.0 |             |
| [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472) | `TestCase::assertStringNotMatchesFormatFile()` | 10.4.0 |             |

#### Test Double API

| Issue                                                             | Description                                                                    | Since  | Replacement                                                                             |
|-------------------------------------------------------------------|--------------------------------------------------------------------------------|--------|-----------------------------------------------------------------------------------------|
| [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240) | `TestCase::createTestProxy()`                                                  | 10.1.0 |                                                                                         |
| [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241) | `TestCase::getMockForAbstractClass()`                                          | 10.1.0 |                                                                                         |
| [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242) | `TestCase::getMockFromWsdl()`                                                  | 10.1.0 |                                                                                         |
| [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243) | `TestCase::getMockForTrait()`                                                  | 10.1.0 |                                                                                         |
| [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244) | `TestCase::getObjectForTrait()`                                                | 10.1.0 |                                                                                         |
| [#5305](https://github.com/sebastianbergmann/phpunit/issues/5305) | `MockBuilder::getMockForAbstractClass()`                                       | 10.1.0 |                                                                                         |
| [#5306](https://github.com/sebastianbergmann/phpunit/issues/5306) | `MockBuilder::getMockForTrait()`                                               | 10.1.0 |                                                                                         |
| [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307) | `MockBuilder::disableProxyingToOriginalMethods()`                              | 10.1.0 |                                                                                         |
| [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307) | `MockBuilder::enableProxyingToOriginalMethods()`                               | 10.1.0 |                                                                                         |
| [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307) | `MockBuilder::setProxyTarget()`                                                | 10.1.0 |                                                                                         |
| [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308) | `MockBuilder::allowMockingUnknownTypes()`                                      | 10.1.0 |                                                                                         |
| [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308) | `MockBuilder::disallowMockingUnknownTypes()`                                   | 10.1.0 |                                                                                         |
| [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309) | `MockBuilder::disableAutoload()`                                               | 10.1.0 |                                                                                         |
| [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309) | `MockBuilder::enableAutoload()`                                                | 10.1.0 |                                                                                         |
| [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315) | `MockBuilder::disableArgumentCloning()`                                        | 10.1.0 |                                                                                         |
| [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315) | `MockBuilder::enableArgumentCloning()`                                         | 10.1.0 |                                                                                         |
| [#5320](https://github.com/sebastianbergmann/phpunit/issues/5320) | `MockBuilder::addMethods()`                                                    | 10.1.0 |                                                                                         |
| [#5415](https://github.com/sebastianbergmann/phpunit/issues/5415) | Support for doubling interfaces (or classes) that have a method named `method` | 11.0.0 |                                                                                         |
| [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421) | `MockBuilder::disableAutoReturnValueGeneration()`                              | 10.3.0 |                                                                                         |
| [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421) | `MockBuilder::enableAutoReturnValueGeneration()`                               | 10.3.0 |                                                                                         |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::onConsecutiveCalls()`                                               | 10.3.0 | Use `$double->willReturn()` instead of `$double->will($this->onConsecutiveCalls())`     |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::returnArgument()`                                                   | 10.3.0 | Use `$double->willReturnArgument()` instead of `$double->will($this->returnArgument())` |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::returnCallback()`                                                   | 10.3.0 | Use `$double->willReturnCallback()` instead of `$double->will($this->returnCallback())` |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::returnSelf()`                                                       | 10.3.0 | Use `$double->willReturnSelf()` instead of `$double->will($this->returnSelf())`         |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::returnValue()`                                                      | 10.3.0 | Use `$double->willReturn()` instead of `$double->will($this->returnValue())`            |
| [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423) | `TestCase::returnValueMap()`                                                   | 10.3.0 | Use `$double->willReturnMap()` instead of `$double->will($this->returnValueMap())`      |
| [#5535](https://github.com/sebastianbergmann/phpunit/issues/5525) | Configuring expectations using `expects()` on test stubs                       | 11.0.0 | Create a mock object when you need to configure expectations on a test double           |

#### Miscellaneous

| Issue                                                             | Description              | Since  | Replacement            |
|-------------------------------------------------------------------|--------------------------|--------|------------------------|
| [#4505](https://github.com/sebastianbergmann/phpunit/issues/4505) | Metadata in doc-comments | 10.3.0 | Metadata in attributes |
| [#5214](https://github.com/sebastianbergmann/phpunit/issues/5214) | `TestCase::iniSet()`     | 10.3.0 |                        |
| [#5216](https://github.com/sebastianbergmann/phpunit/issues/5216) | `TestCase::setLocale()`  | 10.3.0 |                        |
