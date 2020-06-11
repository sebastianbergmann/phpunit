# Changes in PHPUnit 9.3

All notable changes of the PHPUnit 9.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.3.0] - 2020-08-07

### Changed

* [#4264](https://github.com/sebastianbergmann/phpunit/pull/4264): Refactor logical operator constraints
* `PHPUnit\Framework\TestCase::$backupGlobalsBlacklist` is deprecated, please use `PHPUnit\Framework\TestCase::$backupGlobalsExcludeList` instead
* `PHPUnit\Framework\TestCase::$backupStaticAttributesBlacklist` is deprecated, please use `PHPUnit\Framework\TestCase::$backupStaticAttributesExcludeList` instead
* `PHPUnit\Util\Blacklist` is now deprecated, please use `PHPUnit\Util\ExcludeList` instead
* Using `--whitelist <directory>` to include a directory in code coverage reports is now deprecated, please use `--coverage-filter <directory>` instead
* Using `<filter><whitelist><include>...</include></whitelist></filter` to include a directory in code coverage reports is deprecated, please use `<coverage><include>...</include></coverage>` instead
* Using `<filter><whitelist><exclude>...</exclude></whitelist></filter` to exclude a directory from code coverage reports is deprecated, please use `<coverage><exclude>...</exclude></coverage>` instead
* Using `<filter><whitelist addUncoveredFilesFromWhitelist="false">...</whitelist></filter>` to control whether or not uncovered files should be added to code coverage reports is now deprecated, please use `<coverage includeUncoveredFiles="false">...</coverage>` instead 
* Using `<filter><whitelist processUncoveredFilesFromWhitelist="true">...</whitelist></filter>` to control whether or not uncovered files should be processed for code coverage reporting is now deprecated, please use `<coverage processUncoveredFiles="true">...</coverage>` instead 
* Using `<logging><log type="coverage-clover" target="clover.xml"/></logging>` to configure the Clover XML code coverage report is deprecated, please use `<coverage><report><clover outputFile="clover.xml"/></report></coverage>` instead
* Using `<logging><log type="coverage-crap4j" target="crap4j.xml" threshold="50"/></logging>` to configure the Crap4J XML code coverage report is deprecated, please use `<coverage><report><crap4j outputFile="crap4j.xml" threshold="50"/></report></coverage>` instead
* Using `<logging><log type="coverage-html" target="coverage" lowUpperBound="50" highLowerBound="90"/></logging>` to configure the HTML code coverage report is deprecated, please use `<coverage><report><html outputDirectory="coverage" lowUpperBound="50" highLowerBound="90"/></report></coverage>` instead
* Using `<logging><log type="coverage-php" target="coverage.php"/></logging>` to configure the PHP code coverage report is deprecated, please use `<coverage><report><php outputFile="coverage.php"/></report></coverage>` instead
* Using `<logging><log type="coverage-text" target="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/></logging>` to configure the Text code coverage report is deprecated, please use `<coverage><report><text outputFile="coverage.txt" showUncoveredFiles="false" showOnlySummary="true"/></report></coverage>` instead
* Using `<logging><log type="coverage-xml" target="coverage"/></logging>` to configure the XML code coverage report is deprecated, please use `<coverage><report><xml outputDirectory="coverage"/></report></coverage>` instead

[9.3.0]: https://github.com/sebastianbergmann/phpunit/compare/9.2...master
