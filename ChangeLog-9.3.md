# Changes in PHPUnit 9.3

All notable changes of the PHPUnit 9.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.3.0] - 2020-08-07

### Added

* [#3936](https://github.com/sebastianbergmann/phpunit/pull/3936): Support using `@depends` to depend on classes
* [#4260](https://github.com/sebastianbergmann/phpunit/issues/4260): `pathCoverage` attribute on the `phpunit/coverage` element of the XML configuration file for enabling path coverage for code coverage drivers that support it
* [#4314](https://github.com/sebastianbergmann/phpunit/issues/4314): Add option to exit with exit code `1` when no tests are executed

### Changed

* [#4226](https://github.com/sebastianbergmann/phpunit/issues/4226): Deprecate `--dump-xdebug-filter` and `--prepend`
* [#4264](https://github.com/sebastianbergmann/phpunit/pull/4264): Refactor logical operator constraints
* `PHPUnit\Framework\TestCase::$backupGlobalsBlacklist` is now deprecated, please use `PHPUnit\Framework\TestCase::$backupGlobalsExcludeList` instead
* `PHPUnit\Framework\TestCase::$backupStaticAttributesBlacklist` is now deprecated, please use `PHPUnit\Framework\TestCase::$backupStaticAttributesExcludeList` instead
* `PHPUnit\Util\Blacklist` is now deprecated, please use `PHPUnit\Util\ExcludeList` instead
* Using `--whitelist <directory>` to include a directory in code coverage reports is now deprecated, please use `--coverage-filter <directory>` instead
* Using `<filter><whitelist><include>...</include></whitelist></filter` to include a directory in code coverage reports is now deprecated, please use `<coverage><include>...</include></coverage>` instead
* Using `<filter><whitelist><exclude>...</exclude></whitelist></filter` to exclude a directory from code coverage reports is now deprecated, please use `<coverage><exclude>...</exclude></coverage>` instead
* Using `<filter><whitelist addUncoveredFilesFromWhitelist="false">...</whitelist></filter>` to control whether or not uncovered files should be added to code coverage reports is now deprecated, please use `<coverage includeUncoveredFiles="false">...</coverage>` instead 
* Using `<filter><whitelist processUncoveredFilesFromWhitelist="true">...</whitelist></filter>` to control whether or not uncovered files should be processed for code coverage reporting is now deprecated, please use `<coverage processUncoveredFiles="true">...</coverage>` instead 
* Using `<phpunit cacheTokens="true">...</phpunit>` to configure the token cache (which can reduce the time needed to process multiple code coverage reports) is now deprecated, please use `<phpunit><coverage cacheTokens="true">...</coverage></phpunit>` instead
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
* `--generate-configuration` generates a configuration file with `failOnRisky="true"` and `failOnWarning="true"`

[9.3.0]: https://github.com/sebastianbergmann/phpunit/compare/9.2...master
