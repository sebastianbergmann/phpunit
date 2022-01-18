# Changes in PHPUnit 10.0

All notable changes of the PHPUnit 10.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.0.0] - 2021-MM-DD

### Added

* [#4502](https://github.com/sebastianbergmann/phpunit/issues/4502): Support PHP 8 attributes for adding metadata to test classes and test methods as well as tested code units
* [#4641](https://github.com/sebastianbergmann/phpunit/issues/4641): `assertStringEqualsStringIgnoringLineEndings()` and `assertStringContainsStringIgnoringLineEndings()`
* [#4650](https://github.com/sebastianbergmann/phpunit/issues/4650): Support dist file name `phpunit.dist.xml`
* [#4657](https://github.com/sebastianbergmann/phpunit/pull/4657): `--exclude-testsuite` option
* [#4709](https://github.com/sebastianbergmann/phpunit/issues/4709): Support `never` type in test double code generator
* [#4737](https://github.com/sebastianbergmann/phpunit/issues/4737): Support intersection types in test double code generator
* [#4818](https://github.com/sebastianbergmann/phpunit/pull/4818): `assertArrayIsList`
* `@excludeGlobalVariableFromBackup variable` annotation for excluding a global variable from the backup/restore of global and super-global variables
* `#[ExcludeGlobalVariableFromBackup('variable')]` attribute for excluding a global variable from the backup/restore of global and super-global variables
* `@excludeStaticPropertyFromBackup className propertyName` annotation for excluding a static property from the backup/restore of static properties in user-defined classes
* `#[ExcludeStaticPropertyFromBackup('className', 'propertyName')]` attribute for excluding a static property from the backup/restore of static properties in user-defined classes
* `--trace-text <file>` option that controls streaming of event information in text format to a file
* `--no-output` option to disable the output

### Changed

* [#3871](https://github.com/sebastianbergmann/phpunit/issues/3871): Declare return types for `InvocationStubber` methods
* [#3954](https://github.com/sebastianbergmann/phpunit/issues/3954): Disable global state preservation for process isolation by default
* [#4599](https://github.com/sebastianbergmann/phpunit/issues/4599): Unify cache configuration
* [#4603](https://github.com/sebastianbergmann/phpunit/issues/4603): Use "property" instead of "attribute" for configuring the backup of static fields
* [#4656](https://github.com/sebastianbergmann/phpunit/issues/4656): Prevent doubling of `__destruct()`
* PHPUnit no longer invokes a static method named `suite` on a class that is declared in a file that is passed as an argument to the CLI test runner
* PHPUnit no longer promotes variables that are global in the bootstrap script's scope to global variables in the test runner's scope (use `$GLOBALS['variable'] = 'value'` instead of `$variable = 'value'` in your bootstrap script)
* `PHPUnit\Framework\TestCase::$backupGlobals` can no longer be used to enable or disable the backup/restore of global and super-global variables for a test case class
* `PHPUnit\Framework\TestCase::$backupStaticAttributes` can no longer be used to enable or disable the backup/restore of static properties in user-defined classes for a test case class
* `@author` is no longer an alias for `@group`
* The `status` attribute of `<test>` elements in the TestDox XML logfile now contains a textual representation instead of a number (`"success"` instead of `"0"`, for instance)
* The `size` attribute of `<test>` elements in the TestDox XML logfile now contains a textual representation instead of a number (`"unknown"` instead of `"-1"`, for instance)
* The JUnit XML logfile now has both `name` and `file` attributes on `<testcase>` elements for PHPT tests
* The `forceCoversAnnotation` attribute of the `<phpunit>` element of PHPUnit's XML configuration file has been renamed to `requireCoverageMetadata`
* The `beStrictAboutCoversAnnotation` attribute of the `<phpunit>` element of PHPUnit's XML configuration file has been renamed to `beStrictAboutCoverageMetadata`

### Removed

* [#3389](https://github.com/sebastianbergmann/phpunit/issues/3389): Removed `PHPUnit\Framework\TestListener` and `PHPUnit\Framework\TestListenerDefaultImplementation`
* [#3631](https://github.com/sebastianbergmann/phpunit/issues/3631): Remove support for `"ClassName<*>"` as values for `@covers` and `@uses` annotations
* [#3769](https://github.com/sebastianbergmann/phpunit/issues/3769): Remove `MockBuilder::setMethods()` and `MockBuilder::setMethodsExcept()`
* [#3777](https://github.com/sebastianbergmann/phpunit/issues/3777): Remove `PHPUnit\Framework\Error\*` classes
* [#3870](https://github.com/sebastianbergmann/phpunit/issues/3870): Drop support for PHP 7.3
* [#4219](https://github.com/sebastianbergmann/phpunit/issues/4219): Drop support for PHP 7.4
* [#4063](https://github.com/sebastianbergmann/phpunit/issues/4063): Remove `assertNotIsReadable()`
* [#4066](https://github.com/sebastianbergmann/phpunit/issues/4066): Remove `assertNotIsWritable()`
* [#4069](https://github.com/sebastianbergmann/phpunit/issues/4069): Remove `assertDirectoryNotExists()`
* [#4072](https://github.com/sebastianbergmann/phpunit/issues/4072): Remove `assertDirectoryNotIsReadable()`
* [#4075](https://github.com/sebastianbergmann/phpunit/issues/4075): Remove `assertDirectoryNotIsWritable()`
* [#4078](https://github.com/sebastianbergmann/phpunit/issues/4078): Remove `assertFileNotExists()`
* [#4081](https://github.com/sebastianbergmann/phpunit/issues/4081): Remove `assertFileNotIsReadable()`
* [#4087](https://github.com/sebastianbergmann/phpunit/issues/4087): Remove `assertRegExp()`
* [#4090](https://github.com/sebastianbergmann/phpunit/issues/4090): Remove `assertNotRegExp()`
* [#4092](https://github.com/sebastianbergmann/phpunit/issues/4092): Remove `assertEqualXMLStructure()`
* [#4142](https://github.com/sebastianbergmann/phpunit/issues/4142): Remove Prophecy integration
* [#4227](https://github.com/sebastianbergmann/phpunit/issues/4227): Remove `--dump-xdebug-filter` and `--prepend`
* [#4272](https://github.com/sebastianbergmann/phpunit/issues/4272): Remove `PHPUnit\Util\Blacklist`
* [#4273](https://github.com/sebastianbergmann/phpunit/issues/4273): Remove `PHPUnit\Framework\TestCase::$backupGlobalsBlacklist`
* [#4274](https://github.com/sebastianbergmann/phpunit/issues/4274): Remove `PHPUnit\Framework\TestCase::$backupStaticAttributesBlacklist`
* [#4278](https://github.com/sebastianbergmann/phpunit/issues/4278): Remove `--whitelist` option
* [#4279](https://github.com/sebastianbergmann/phpunit/issues/4279): Remove support for old code coverage configuration
* [#4286](https://github.com/sebastianbergmann/phpunit/issues/4286): Remove support for old logging configuration
* [#4298](https://github.com/sebastianbergmann/phpunit/issues/4298): Remove `at()` matcher
* [#4395](https://github.com/sebastianbergmann/phpunit/issues/4395): Remove `Command::createRunner()`
* [#4397](https://github.com/sebastianbergmann/phpunit/issues/4397): Remove confusing parameter options for XML assertions
* [#4531](https://github.com/sebastianbergmann/phpunit/pull/4531): Remove `--loader` option as well as `testSuiteLoaderClass` and `testSuiteLoaderFile` XML configuration settings
* [#4536](https://github.com/sebastianbergmann/phpunit/issues/4536): Remove `assertFileNotIsWritable()`
* [#4596](https://github.com/sebastianbergmann/phpunit/issues/4595): Remove Test Hooks
* [#4564](https://github.com/sebastianbergmann/phpunit/issues/4564): Deprecate `withConsecutive()`
* [#4567](https://github.com/sebastianbergmann/phpunit/issues/4567): Deprecate support for generators in `assertCount()` and `Count` constraint
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): Deprecate assertions that operate on class/object properties
* Removed the `PHPUnit\Runner\TestSuiteLoader` interface
* Removed the `<listeners>` XML configuration element and its children
* Removed the `groups` attribute on the `<test>` element in the TestDox XML report
* Removed the `beStrictAboutResourceUsageDuringSmallTests` attribute on the `<phpunit>` XML configuration element and the `--disallow-resource-usage` option as well as the feature they used to control
* Removed the `beStrictAboutTodoAnnotatedTests` attribute on the `<phpunit>` XML configuration element and the `--disallow-todo-tests` option as well as the feature they used to control
* Removed the `processUncoveredFiles` attribute on the `<coverage>` XML configuration element
* Removed the `PHPUnit\Framework\TestCase::getMockClass()` method
* Removed the `PHPUnit\Framework\TestCase::$backupGlobalsExcludeList` property, use the `@excludeGlobalVariableFromBackup variable` annotation or the `#[ExcludeGlobalVariableFromBackup('variable')]` attribute instead for excluding a global variable from the backup/restore of global and super-global variables
* Removed the `PHPUnit\Framework\TestCase::$backupStaticAttributesExcludeList` property, use the `@excludeStaticPropertyFromBackup className propertyName` annotation or the `#[ExcludeStaticPropertyFromBackup('className', 'propertyName')]` attribute instead for excluding a static property from the backup/restore of static properties in user-defined classes
* Removed the `PHPUnit\Framework\TestCase::$preserveGlobalState` property, use the `@preserveGlobalState enabled` annotation or the `#[PreserveGlobalState(true)]` attribute instead for enabling the preservation of global state when running tests in isolation
* Removed the `--debug` option
* Removed the `--extensions` option
* Removed the `--printer` option
* Removed the `printerClass` and `printerFile` attributes on the `<phpunit>` XML configuration element
* The CLI test runner can no longer be extended through inheritance, the `PHPUnit\TextUI\Command` class has been removed

[10.0.0]: https://github.com/sebastianbergmann/phpunit/compare/9.5...master
