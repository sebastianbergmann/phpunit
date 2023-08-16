# Deprecations

## Soft Deprecations

This functionality is currently [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation):

### Writing Tests

#### Test Double API

* [#3687](https://github.com/sebastianbergmann/phpunit/issues/3687): `MockBuilder::setMethods()` (since PHPUnit 8.3.0)
* [#3687](https://github.com/sebastianbergmann/phpunit/issues/3687): `MockBuilder::setMethodsExcept()` (since PHPUnit 9.6.0)

## Hard Deprecations

This functionality is currently [hard-deprecated](https://phpunit.de/backward-compatibility.html#hard-deprecation):

### Writing Tests

#### Assertions, Constraints, and Expectations

* [#4062](https://github.com/sebastianbergmann/phpunit/issues/4062): `TestCase::assertNotIsReadable()` (since PHPUnit 9.1.0)
* [#4065](https://github.com/sebastianbergmann/phpunit/issues/4065): `TestCase::assertNotIsWritable()` (since PHPUnit 9.1.0)
* [#4068](https://github.com/sebastianbergmann/phpunit/issues/4068): `TestCase::assertDirectoryNotExists()` (since PHPUnit 9.1.0)
* [#4071](https://github.com/sebastianbergmann/phpunit/issues/4071): `TestCase::assertDirectoryNotIsReadable()` (since PHPUnit 9.1.0)
* [#4074](https://github.com/sebastianbergmann/phpunit/issues/4074): `TestCase::assertDirectoryNotIsWritable()` (since PHPUnit 9.1.0)
* [#4077](https://github.com/sebastianbergmann/phpunit/issues/4077): `TestCase::assertFileNotExists()` (since PHPUnit 9.1.0)
* [#4080](https://github.com/sebastianbergmann/phpunit/issues/4080): `TestCase::assertFileNotIsReadable()` (since PHPUnit 9.1.0)
* [#4083](https://github.com/sebastianbergmann/phpunit/issues/4083): `TestCase::assertFileNotIsWritable()` (since PHPUnit 9.1.0)
* [#4086](https://github.com/sebastianbergmann/phpunit/issues/4086): `TestCase::assertRegExp()` (since PHPUnit 9.1.0)
* [#4089](https://github.com/sebastianbergmann/phpunit/issues/4089): `TestCase::assertNotRegExp()` (since PHPUnit 9.1.0)
* [#4091](https://github.com/sebastianbergmann/phpunit/issues/4091): `TestCase::assertEqualXMLStructure()` (since PHPUnit 9.1.0)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertClassHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertClassNotHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertClassHasStaticAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertClassNotHasStaticAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertObjectHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::assertObjectNotHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::classHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::classHasStaticAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `TestCase::objectHasAttribute()` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `ClassHasAttribute` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `ClassHasStaticAttribute` (since PHPUnit 9.6.1)
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): `ObjectHasAttribute` (since PHPUnit 9.6.1)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectDeprecation()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectDeprecationMessage()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectDeprecationMessageMatches()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectError()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectErrorMessage()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectErrorMessageMatches()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectNotice()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectNoticeMessage()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectNoticeMessageMatches()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectWarning()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectWarningMessage()` (since PHPUnit 9.6.0)
* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): `TestCase::expectWarningMessageMatches()` (since PHPUnit 9.6.0)

#### Test Double API

* [#4141](https://github.com/sebastianbergmann/phpunit/issues/4141): `TestCase::prophesize()` (since PHPUnit 9.1.0)
* [#4297](https://github.com/sebastianbergmann/phpunit/issues/4297): `TestCase::at()` (since PHPUnit 9.3.0)
* [#4297](https://github.com/sebastianbergmann/phpunit/issues/4297): `InvokedAtIndex` (since PHPUnit 9.3.0)
* [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063): `InvocationMocker::withConsecutive()` (since PHPUnit 9.6.0)
* [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063): `ConsecutiveParameters` (since PHPUnit 9.6.0)
* [#5064](https://github.com/sebastianbergmann/phpunit/issues/5064): `TestCase::getMockClass()` (since PHPUnit 9.6.0)

#### Miscellaneous

* [#5132](https://github.com/sebastianbergmann/phpunit/issues/5132): `Test` suffix for abstract test case classes
* `TestCase::$backupGlobalsBlacklist` (since PHPUnit 9.3.0)
* `TestCase::$backupStaticAttributesBlacklist` (since PHPUnit 9.3.0)

### Extending PHPUnit

* [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039): `Command::handleLoader()` (since PHPUnit 9.1.0)
* [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039): `TestSuiteLoader` (since PHPUnit 9.1.0)
* [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039): `StandardTestSuiteLoader` (since PHPUnit 9.1.0)
* [#4676](https://github.com/sebastianbergmann/phpunit/issues/4676): `TestListener` (since PHPUnit 8.0.0)
* [#4676](https://github.com/sebastianbergmann/phpunit/issues/4676): `TestListenerDefaultImplementation` (since PHPUnit 8.2.4)
