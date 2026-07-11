--TEST--
A test runner warning is emitted for each trigger-specific failOn*Deprecation setting that is explicitly set to "false" in the XML configuration file while failOnDeprecation="true" is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/fail-on-deprecation-precedence/phpunit-all-trigger-fail-on-false.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit-all-trigger-fail-on-false.xml

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit test runner warnings:

1) failOnSelfDeprecation="false" has no effect because failOnDeprecation is enabled. Use the --do-not-fail-on-self-deprecation CLI option instead

2) failOnDirectDeprecation="false" has no effect because failOnDeprecation is enabled. Use the --do-not-fail-on-direct-deprecation CLI option instead

3) failOnIndirectDeprecation="false" has no effect because failOnDeprecation is enabled. Use the --do-not-fail-on-indirect-deprecation CLI option instead

--

1 test triggered 1 deprecation:

1) %sFirstParty.php:%d
deprecation in first-party code

Triggered by:

* PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation
  %sSelfDeprecationTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 3, Deprecations: 1.
