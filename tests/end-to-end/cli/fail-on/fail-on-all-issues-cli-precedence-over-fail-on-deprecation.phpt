--TEST--
--fail-on-all-issues takes precedence over failOnDeprecation="false" in the XML configuration file; a test runner warning is emitted for the setting that has no effect
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--fail-on-all-issues';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/fail-on-all-issues-precedence/phpunit-fail-on-deprecation-false.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit-fail-on-deprecation-false.xml

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) failOnDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-deprecation CLI option instead

--

1 test triggered 1 deprecation:

1) %sFirstParty.php:%d
deprecation in first-party code

Triggered by:

* PHPUnit\TestFixture\FailOnAllIssuesPrecedence\SelfDeprecationTest::testSelfDeprecation
  %sSelfDeprecationTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1, Deprecations: 1.
