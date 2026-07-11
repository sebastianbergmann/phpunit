--TEST--
A test runner warning is emitted for each fine-grained failOn* setting that is explicitly set to "false" in the XML configuration file while failOnAllIssues="true" is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/fail-on-all-issues-precedence/phpunit-all-fine-grained-fail-on-false.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit-all-fine-grained-fail-on-false.xml

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 13 PHPUnit test runner warnings:

1) failOnDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-deprecation CLI option instead

2) failOnSelfDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-self-deprecation CLI option instead

3) failOnDirectDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-direct-deprecation CLI option instead

4) failOnIndirectDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-indirect-deprecation CLI option instead

5) failOnPhpunitDeprecation="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-phpunit-deprecation CLI option instead

6) failOnPhpunitNotice="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-phpunit-notice CLI option instead

7) failOnPhpunitWarning="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-phpunit-warning CLI option instead

8) failOnEmptyTestSuite="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-empty-test-suite CLI option instead

9) failOnIncomplete="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-incomplete CLI option instead

10) failOnNotice="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-notice CLI option instead

11) failOnRisky="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-risky CLI option instead

12) failOnSkipped="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-skipped CLI option instead

13) failOnWarning="false" has no effect because failOnAllIssues is enabled. Use the --do-not-fail-on-warning CLI option instead

--

1 test triggered 1 deprecation:

1) %sFirstParty.php:%d
deprecation in first-party code

Triggered by:

* PHPUnit\TestFixture\FailOnAllIssuesPrecedence\SelfDeprecationTest::testSelfDeprecation
  %sSelfDeprecationTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 13, Deprecations: 1.
