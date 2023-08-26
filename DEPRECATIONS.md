# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Writing Tests

#### Test Double API

| Issue                                                             | Description                       | Since | Replacement |
|-------------------------------------------------------------------|-----------------------------------|-------|-------------|
| [#3687](https://github.com/sebastianbergmann/phpunit/issues/3687) | `MockBuilder::setMethods()`       | 8.3.0 |             |
| [#3687](https://github.com/sebastianbergmann/phpunit/issues/3687) | `MockBuilder::setMethodsExcept()` | 9.6.0 |             | 

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

| Issue                                                             | Description                                    | Since | Replacement                                       |
|-------------------------------------------------------------------|------------------------------------------------|-------|---------------------------------------------------|
| [#4062](https://github.com/sebastianbergmann/phpunit/issues/4062) | `TestCase::assertNotIsReadable()`              | 9.1.0 | `TestCase::assertIsNotReadable()`                 |
| [#4065](https://github.com/sebastianbergmann/phpunit/issues/4065) | `TestCase::assertNotIsWritable()`              | 9.1.0 | `TestCase::assertIsNotWritable()`                 |
| [#4068](https://github.com/sebastianbergmann/phpunit/issues/4068) | `TestCase::assertDirectoryNotExists()`         | 9.1.0 | `TestCase::assertDirectoryDoesNotExist()`         |
| [#4071](https://github.com/sebastianbergmann/phpunit/issues/4071) | `TestCase::assertDirectoryNotIsReadable()`     | 9.1.0 | `TestCase::assertDirectoryIsNotReadable()`        |
| [#4074](https://github.com/sebastianbergmann/phpunit/issues/4074) | `TestCase::assertDirectoryNotIsWritable()`     | 9.1.0 | `TestCase::assertDirectoryIsNotWritable()`        |
| [#4077](https://github.com/sebastianbergmann/phpunit/issues/4077) | `TestCase::assertFileNotExists()`              | 9.1.0 | `TestCase::assertFileDoesNotExist()`              |
| [#4080](https://github.com/sebastianbergmann/phpunit/issues/4080) | `TestCase::assertFileNotIsReadable()`          | 9.1.0 | `TestCase::assertFileIsNotReadable()`             |
| [#4083](https://github.com/sebastianbergmann/phpunit/issues/4083) | `TestCase::assertFileNotIsWritable()`          | 9.1.0 | `TestCase::assertFileIsNotWritable()`             |
| [#4086](https://github.com/sebastianbergmann/phpunit/issues/4086) | `TestCase::assertRegExp()`                     | 9.1.0 | `TestCase::assertMatchesRegularExpression()`      |
| [#4089](https://github.com/sebastianbergmann/phpunit/issues/4089) | `TestCase::assertNotRegExp()`                  | 9.1.0 | `TestCase::assertDoesNotMatchRegularExpression()` |
| [#4091](https://github.com/sebastianbergmann/phpunit/issues/4091) | `TestCase::assertEqualXMLStructure()`          | 9.1.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectDeprecation()`                | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectDeprecationMessage()`         | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectDeprecationMessageMatches()`  | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectError()`                      | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectErrorMessage()`               | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectErrorMessageMatches()`        | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectNotice()`                     | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectNoticeMessage()`              | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectNoticeMessageMatches()`       | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectWarning()`                    | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectWarningMessage()`             | 9.6.0 |                                                   |
| [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062) | `TestCase::expectWarningMessageMatches()`      | 9.6.0 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertClassHasAttribute()`          | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertClassNotHasAttribute()`       | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertClassHasStaticAttribute()`    | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertClassNotHasStaticAttribute()` | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertObjectHasAttribute()`         | 9.6.1 | `TestCase::assertObjectHasProperty()`             |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::assertObjectNotHasAttribute()`      | 9.6.1 | `TestCase::assertObjectNotHasProperty()`          |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::classHasAttribute()`                | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::classHasStaticAttribute()`          | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `TestCase::objectHasAttribute()`               | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `ClassHasAttribute`                            | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `ClassHasStaticAttribute`                      | 9.6.1 |                                                   |
| [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601) | `ObjectHasAttribute`                           | 9.6.1 | `ObjectHasProperty`                               |

#### Test Double API

| Issue                                                             | Description                           | Since | Replacement                                                             |
|-------------------------------------------------------------------|---------------------------------------|-------|-------------------------------------------------------------------------|
| [#4141](https://github.com/sebastianbergmann/phpunit/issues/4141) | `TestCase::prophesize()`              | 9.1.0 | [phpspec/prophecy-phpunit](https://github.com/phpspec/prophecy-phpunit) |
| [#4297](https://github.com/sebastianbergmann/phpunit/issues/4297) | `TestCase::at()`                      | 9.3.0 |                                                                         |
| [#4297](https://github.com/sebastianbergmann/phpunit/issues/4297) | `InvokedAtIndex`                      | 9.3.0 |                                                                         |
| [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063) | `InvocationMocker::withConsecutive()` | 9.6.0 |                                                                         |
| [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063) | `ConsecutiveParameters`               | 9.6.0 |                                                                         |
| [#5064](https://github.com/sebastianbergmann/phpunit/issues/5064) | `TestCase::getMockClass()`            | 9.6.0 |                                                                         |

#### Miscellaneous

| Issue                                                             | Description                                  | Since | Replacement                                    |
|-------------------------------------------------------------------|----------------------------------------------|-------|------------------------------------------------|
| [#5132](https://github.com/sebastianbergmann/phpunit/issues/5132) | `Test` suffix for abstract test case classes |       |                                                |
|                                                                   | `TestCase::$backupGlobalsBlacklist`          | 9.3.0 | `TestCase::$backupGlobalsExcludeList`          |
|                                                                   | `TestCase::$backupStaticAttributesBlacklist` | 9.3.0 | `TestCase::$backupStaticAttributesExcludeList` |

### Extending PHPUnit

| Issue                                                             | Description                          | Since | Replacement                                                 |
|-------------------------------------------------------------------|--------------------------------------|-------|-------------------------------------------------------------|
| [#4676](https://github.com/sebastianbergmann/phpunit/issues/4676) | `TestListener`                       | 8.0.0 | [Event System](https://docs.phpunit.de/en/10.3/events.html) |
| [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039) | `Command::handleLoader()`            | 9.1.0 |                                                             |
| [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039) | `TestSuiteLoader`                    | 9.1.0 |                                                             |
| [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039) | `StandardTestSuiteLoader`            | 9.1.0 |                                                             |
| [#4676](https://github.com/sebastianbergmann/phpunit/issues/4676) | `TestListenerDefaultImplementation`  | 8.2.4 | [Event System](https://docs.phpunit.de/en/10.3/events.html) |
