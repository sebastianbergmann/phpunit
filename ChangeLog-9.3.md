# Changes in PHPUnit 9.3

All notable changes of the PHPUnit 9.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.3.11] - 2020-09-24

* No changes; `phpunit.phar` rebuilt with updated dependencies

## [9.3.10] - 2020-09-12

### Fixed

* [#4453](https://github.com/sebastianbergmann/phpunit/issues/4453): `--migrate-configuration` can only migrate `phpunit.xml` or `phpunit.xml.dist`
* [#4454](https://github.com/sebastianbergmann/phpunit/issues/4454): "Migration failed" message shown when trying to migrate XML configuration file that does not need migration

## [9.3.9] - 2020-09-11

### Fixed

* [#4451](https://github.com/sebastianbergmann/phpunit/pull/4451): Incorrect signature generated for test double methods with nullable union types

## [9.3.8] - 2020-08-27

### Fixed

* [#3937](https://github.com/sebastianbergmann/phpunit/issues/3937): Risky tests are inconsistently reported in JUnit XML
* [#4435](https://github.com/sebastianbergmann/phpunit/issues/4435): Global assert wrappers break preloading

## [9.3.7] - 2020-08-11

### Fixed

* [#4419](https://github.com/sebastianbergmann/phpunit/issues/4419): `--migrate-configuration` cannot migrate XML configuration files with multiple `<exclude>` elements
* [#4422](https://github.com/sebastianbergmann/phpunit/issues/4422): Error when `libxml_disable_entity_loader()` is used in a data provider

## [9.3.6] - 2020-08-11

### Fixed

* [#4417](https://github.com/sebastianbergmann/phpunit/issues/4417): Default value for `<coverage includeUncoveredFiles="true|false">` XML configuration attribute was `false` instead of `true`

## [9.3.5] - 2020-08-10

### Fixed

* [#4412](https://github.com/sebastianbergmann/phpunit/issues/4412): Code Coverage does not work for isolated test when PHAR is used

## [9.3.4] - 2020-08-10

### Added

* Added `--coverage-cache <directory>` CLI option for enabling a cache for static analysis results; it will write its files to `<directory>`
* Added `<coverage cacheDirectory="directory">` XML configuration attribute for enabling a cache for static analysis results; it will write its files to `directory`
* Added `--warm-coverage-cache` CLI option for warming the cache for static analysis results; the cache must be configured for this to work

### Fixed

* [#4405](https://github.com/sebastianbergmann/phpunit/issues/4405): Location for (current version of) XML Schema for XML configuration file has changed
* Default value for `<phpunit cacheResult="true|false">` XML configuration attribute was `false` instead of `true`

## [9.3.3] - 2020-08-08

### Changed

* Reverted the workaround for [#4399](https://github.com/sebastianbergmann/phpunit/issues/4399) now that the root cause is fixed in PHP-Scoper

### Fixed

* [#4404](https://github.com/sebastianbergmann/phpunit/issues/4404): Code Coverage does not work with PHAR and Xdebug
* [#4407](https://github.com/sebastianbergmann/phpunit/issues/4407): PHPUnit 9.3 breaks backward compatibility for `assertXmlStringEqualsXmlFile()`

## [9.3.2] - 2020-08-07

### Fixed

* [#4402](https://github.com/sebastianbergmann/phpunit/issues/4402): `--no-logging` has no effect

## [9.3.1] - 2020-08-07

### Fixed

* [#4399](https://github.com/sebastianbergmann/phpunit/issues/4399): PHAR of PHPUnit 9.3 is broken

## [9.3.0] - 2020-08-07

### Added

* [#3936](https://github.com/sebastianbergmann/phpunit/pull/3936): Support using `@depends` to depend on classes
* [#4260](https://github.com/sebastianbergmann/phpunit/issues/4260): `pathCoverage` attribute on the `phpunit/coverage` element of the XML configuration file for enabling path coverage for code coverage drivers that support it
* [#4288](https://github.com/sebastianbergmann/phpunit/issues/4288): Add option for migrating `phpunit.xml` from a supported but outdated XML schema version
* [#4314](https://github.com/sebastianbergmann/phpunit/issues/4314): Add option to exit with exit code `1` when no tests are executed
* [#4325](https://github.com/sebastianbergmann/phpunit/issues/4325): Support PHP 8
* [#4365](https://github.com/sebastianbergmann/phpunit/pull/4365): `assertIsClosedResource()` and `assertIsNotClosedResource()`
* `--path-coverage` CLI option for enabling path coverage for code coverage drivers that support it

### Changed

* [#4226](https://github.com/sebastianbergmann/phpunit/issues/4226): Deprecate `--dump-xdebug-filter` and `--prepend`
* [#4264](https://github.com/sebastianbergmann/phpunit/pull/4264): Refactor logical operator constraints
* [#4365](https://github.com/sebastianbergmann/phpunit/pull/4365): `assertIsResource()` and `assertIsNotResource()` now handle closed resources
* `PHPUnit\Framework\TestCase::$backupGlobalsBlacklist` is now deprecated, please use `PHPUnit\Framework\TestCase::$backupGlobalsExcludeList` instead
* `PHPUnit\Framework\TestCase::$backupStaticAttributesBlacklist` is now deprecated, please use `PHPUnit\Framework\TestCase::$backupStaticAttributesExcludeList` instead
* `PHPUnit\Util\Blacklist` is now deprecated, please use `PHPUnit\Util\ExcludeList` instead
* Using `--whitelist <directory>` to include a directory in code coverage reports is now deprecated, please use `--coverage-filter <directory>` instead
* Using `--generate-configuration` now generates a configuration file with `failOnRisky="true"` and `failOnWarning="true"`

#### Configuration of Code Coverage and Logging in `phpunit.xml`

The configuration of code coverage and logging in `phpunit.xml` has been changed to be less confusing. Here is an example of what this configuration looked like prior to PHPUnit 9.3:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.2/phpunit.xsd"
         cacheTokens="true"
         disableCodeCoverageIgnore="true"
         ignoreDeprecatedCodeUnitsFromCodeCoverage="true">
    <!-- ... -->

    <filter>
        <whitelist addUncoveredFilesFromWhitelist="true"
                   processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">src</directory>

            <exclude>
                <directory suffix=".php">src/generated</directory>
                <file>src/autoload.php</file>
            </exclude>
        </whitelist>
    </filter>

    <logging>
        <log type="coverage-html" target="html-coverage" lowUpperBound="50" highLowerBound="90"/>
        <log type="coverage-clover" target="clover.xml"/>
        <log type="coverage-crap4j" threshold="50" target="crap4j.xml"/>
        <log type="coverage-text" showUncoveredFiles="false" showOnlySummary="true" target="coverage.txt"/>
        <log type="junit" target="junit.xml"/>
        <log type="teamcity" target="teamcity.txt"/>
        <log type="testdox-html" target="testdox.html"/>
        <log type="testdox-text" target="testdox.txt"/>
        <log type="testdox-xml" target="testdox.xml"/>
        <log type="plain" target="logfile.txt"/>
    </logging>
</phpunit>
```

A long time ago, PHPUnit supported both an exclude-list ("blacklist"), and an include-list ("whitelist") for filter files for code coverage. Back then, the exclude-list was pre-filled with PHPUnit's own sourcecode files as well as the sourcecode files of its dependencies. When the (back then) optional include-list was configured, the exclude-list was ignored and only the files on the include-list were considered for code coverage. This was confusing, and the exclude-list was removed long ago, but the `<filter><whitelist>` structure was not simplified at the time although it was no longer necessary.

For lack of better terminology, an XML element such as `<log type="coverage-html" target="/path/to/directory"/>` is an implicit API that is not type-safe. It is neither obvious that `coverage-html` is a valid value for `type` nor is it obvious that the value of `target` needs to be a directory.

Some configuration options for code coverage were represented as attributes on the root `<phpunit>` element while others were represented as attributes on the `<whitelist>` element.

Here is what the same configuration looks like in PHPUnit 9.3:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd">
    <!-- ... -->

    <coverage includeUncoveredFiles="true"
              processUncoveredFiles="true"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="true">
        <include>
            <directory suffix=".php">src</directory>
        </include>

        <exclude>
            <directory suffix=".php">src/generated</directory>
            <file>src/autoload.php</file>
        </exclude>

        <report>
            <clover outputFile="clover.xml"/>
            <crap4j outputFile="crap4j.xml" threshold="50"/>
            <html outputDirectory="html-coverage" lowUpperBound="50" highLowerBound="90"/>
            <php outputFile="coverage.php"/>
            <text outputFile="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/>
            <xml outputDirectory="xml-coverage"/>
        </report>
    </coverage>

    <logging>
        <junit outputFile="junit.xml"/>
        <teamcity outputFile="teamcity.txt"/>
        <testdoxHtml outputFile="testdox.html"/>
        <testdoxText outputFile="testdox.txt"/>
        <testdoxXml outputFile="testdox.xml"/>
        <text outputFile="logfile.txt"/>
    </logging>
</phpunit>
```

In the example shown above, the section

```xml
<include>
    <directory suffix=".php">src</directory>
</include>

<exclude>
    <directory suffix=".php">src/generated</directory>
    <file>src/autoload.php</file>
</exclude>
```

configures the following:

* Include all files with `.php` suffix in the `src` directory and its sub-directories in the code coverage report
* But exclude all files with `.php` suffix in the `src/generated` directory and its sub-directories as well as the `src/autoload.php` file from the code coverage report

We believe this new way of configuring code coverage and logging in `phpunit.xml` to be more clear for the users. We already know that the code that parses `phpunit.xml` is now more robust than it was before. We also know that adding support for the configuration of more advanced code coverage options such as path coverage would have made the confusion even worse.

A `phpunit.xml` configuration file that was valid (according to `phpunit.xsd`) for PHPUnit 9.2 will continue to work with PHPUnit 9.3. However, PHPUnit 9.3 will complain about validation errors when it loads the `phpunit.xml` configuration file.

Here is a detailed list of changes for the configuration of code coverage and logging in `phpunit.xml`:

* Using `<filter><whitelist><include>...</include></whitelist></filter` to include a directory in code coverage reports is now deprecated, please use `<coverage><include>...</include></coverage>` instead
* Using `<filter><whitelist><exclude>...</exclude></whitelist></filter` to exclude a directory from code coverage reports is now deprecated, please use `<coverage><exclude>...</exclude></coverage>` instead
* Using `<filter><whitelist addUncoveredFilesFromWhitelist="false">...</whitelist></filter>` to control whether or not uncovered files should be added to code coverage reports is now deprecated, please use `<coverage includeUncoveredFiles="false">...</coverage>` instead 
* Using `<filter><whitelist processUncoveredFilesFromWhitelist="true">...</whitelist></filter>` to control whether or not uncovered files should be processed for code coverage reporting is now deprecated, please use `<coverage processUncoveredFiles="true">...</coverage>` instead 
* Using `<phpunit disableCodeCoverageIgnore="true">...</phpunit>` to configure whether `@coverCoverageIgnore` annotations should be ignored is now deprecated, please use `<phpunit><coverage disableCodeCoverageIgnore="true">...</coverage></phpunit>` instead
* Using `<phpunit ignoreDeprecatedCodeUnitsFromCodeCoverage="true">...</phpunit>` to configure whether code units annotated with `@deprecated` should be ignored is now deprecated, please use `<phpunit><coverage ignoreDeprecatedCodeUnits="true">...</coverage></phpunit>` instead
* Using `<logging><log type="coverage-clover" target="clover.xml"/></logging>` to configure the Clover XML code coverage report is now deprecated, please use `<coverage><report><clover outputFile="clover.xml"/></report></coverage>` instead
* Using `<logging><log type="coverage-crap4j" target="crap4j.xml" threshold="50"/></logging>` to configure the Crap4J XML code coverage report is now deprecated, please use `<coverage><report><crap4j outputFile="crap4j.xml" threshold="50"/></report></coverage>` instead
* Using `<logging><log type="coverage-html" target="coverage" lowUpperBound="50" highLowerBound="90"/></logging>` to configure the HTML code coverage report is now deprecated, please use `<coverage><report><html outputDirectory="coverage" lowUpperBound="50" highLowerBound="90"/></report></coverage>` instead
* Using `<logging><log type="coverage-php" target="coverage.php"/></logging>` to configure the PHP code coverage report is now deprecated, please use `<coverage><report><php outputFile="coverage.php"/></report></coverage>` instead
* Using `<logging><log type="coverage-text" target="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/></logging>` to configure the Text code coverage report is now deprecated, please use `<coverage><report><text outputFile="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/></report></coverage>` instead
* Using `<logging><log type="coverage-xml" target="coverage"/></logging>` to configure the XML code coverage report is now deprecated, please use `<coverage><report><xml outputDirectory="coverage"/></report></coverage>` instead
* Using `<logging><log type="junit" target="junit.xml"/></logging>` to configure the JUnit XML logger is now deprecated, please use `<logging><junit outputFile="junit.xml"/></logging>` instead
* Using `<logging><log type="teamcity" target="teamcity.txt"/></logging>` to configure the TeamCity logger is now deprecated, please use `<logging><teamcity outputFile="teamcity.txt"/></logging>` instead
* Using `<logging><log type="testdox-html" target="testdox.html"/></logging>` to configure the TestDox HTML logger is now deprecated, please use `<logging><testdoxHtml outputFile="testdox.html"/></logging>` instead
* Using `<logging><log type="testdox-text" target="testdox.txt"/></logging>` to configure the TestDox Text logger is now deprecated, please use `<logging><testdoxText outputFile="testdox.txt"/></logging>` instead
* Using `<logging><log type="testdox-xml" target="testdox.xml"/></logging>` to configure the TestDox XML logger is now deprecated, please use `<logging><testdoxXml outputFile="testdox.xml"/></logging>` instead
* Using `<logging><log type="plain" target="logfile.txt"/></logging>` to configure the plain text logger is now deprecated, please use `<logging><text outputFile="logfile.txt"/></logging>` instead

If you have an XML configuration file that validates against [PHPUnit 9.2's XML schema](https://schema.phpunit.de/9.2/phpunit.xsd), then you can use the new `--migrate-configuration` CLI option to automatically migrate your XML configuration file to the new format.

### Removed

* [#4297](https://github.com/sebastianbergmann/phpunit/issues/4297): Deprecate `at()` matcher
* [#4396](https://github.com/sebastianbergmann/phpunit/issues/4396): Deprecate confusing parameter options for XML assertions
* The `cacheTokens` attribute is no longer supported in XML configuration files

[9.3.11]: https://github.com/sebastianbergmann/phpunit/compare/9.3.10...9.3.11
[9.3.10]: https://github.com/sebastianbergmann/phpunit/compare/9.3.9...9.3.10
[9.3.9]: https://github.com/sebastianbergmann/phpunit/compare/9.3.8...9.3.9
[9.3.8]: https://github.com/sebastianbergmann/phpunit/compare/9.3.7...9.3.8
[9.3.7]: https://github.com/sebastianbergmann/phpunit/compare/9.3.6...9.3.7
[9.3.6]: https://github.com/sebastianbergmann/phpunit/compare/9.3.5...9.3.6
[9.3.5]: https://github.com/sebastianbergmann/phpunit/compare/9.3.4...9.3.5
[9.3.4]: https://github.com/sebastianbergmann/phpunit/compare/9.3.3...9.3.4
[9.3.3]: https://github.com/sebastianbergmann/phpunit/compare/9.3.2...9.3.3
[9.3.2]: https://github.com/sebastianbergmann/phpunit/compare/9.3.1...9.3.2
[9.3.1]: https://github.com/sebastianbergmann/phpunit/compare/9.3.0...9.3.1
[9.3.0]: https://github.com/sebastianbergmann/phpunit/compare/9.2...9.3.0
