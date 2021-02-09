# Changes in PHPUnit 10.0

All notable changes of the PHPUnit 10.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.0.0] - 2021-04-02

### Changed

* [#3871](https://github.com/sebastianbergmann/phpunit/issues/3871): Declare return types for `InvocationStubber` methods
* [#4599](https://github.com/sebastianbergmann/phpunit/issues/4599): Unify cache configuration
* [#4603](https://github.com/sebastianbergmann/phpunit/issues/4603): Use "property" instead of "attribute" for configuring the backup of static fields
* PHPUnit no longer invokes a static method named `suite` on a class that is declared in a file that is passed as an argument to the CLI test runner
* PHPUnit no longer promotes variables that are global in the bootstrap script's scope to global variables in the test runner's scope (use `$GLOBALS['variable'] = 'value'` instead of `$variable = 'value'` in your bootstrap script)
* The `status` attribute of `<test>` elements in the TestDox XML logfile now contains a textual representation instead of a number (`"success"` instead of `"0"`, for instance)
* The `size` attribute of `<test>` elements in the TestDox XML logfile now contains a textual representation instead of a number (`"unknown"` instead of `"-1"`, for instance)

### Removed

* [#3389](https://github.com/sebastianbergmann/phpunit/issues/3389): Removed `PHPUnit\Framework\TestListener` and `PHPUnit\Framework\TestListenerDefaultImplementation`
* [#3631](https://github.com/sebastianbergmann/phpunit/issues/3631): Remove support for `"ClassName<*>"` as values for `@covers` and `@uses` annotations
* [#3769](https://github.com/sebastianbergmann/phpunit/issues/3769): Remove `MockBuilder::setMethods()` and `MockBuilder::setMethodsExcept()`
* [#3777](https://github.com/sebastianbergmann/phpunit/issues/3777): Remove `PHPUnit\Framework\Error\*` classes
* [#3870](https://github.com/sebastianbergmann/phpunit/issues/3870): Drop support for PHP 7.3
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
* [#4564](https://github.com/sebastianbergmann/phpunit/issues/4564): Deprecate `withConsecutive()`
* [#4567](https://github.com/sebastianbergmann/phpunit/issues/4567): Deprecate support for generators in `assertCount()` and `Count` constraint
* [#4595](https://github.com/sebastianbergmann/phpunit/issues/4595): Deprecate Test Hooks
* [#4601](https://github.com/sebastianbergmann/phpunit/issues/4601): Deprecate assertions that operate on class/object properties
* Removed the `PHPUnit\Runner\TestSuiteLoader` interface
* Removed the `<listeners>` XML configuration element and its children
* Removed the `groups` attribute on the `test` element in the TestDox XML report

[10.0.0]: https://github.com/sebastianbergmann/phpunit/compare/9.5...master
