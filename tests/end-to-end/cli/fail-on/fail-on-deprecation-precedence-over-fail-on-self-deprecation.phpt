--TEST--
failOnDeprecation="true" takes precedence over failOnSelfDeprecation="false" in the XML configuration file; a test runner warning is emitted for the setting that has no effect
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/fail-on-deprecation-precedence/phpunit-fail-on-self-deprecation-false.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (failOnSelfDeprecation="false" has no effect because failOnDeprecation is enabled. Use the --do-not-fail-on-self-deprecation CLI option instead)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit-fail-on-self-deprecation-false.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation)
Test Prepared (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation)
Test Triggered Deprecation (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation, issue triggered by test code calling into first-party code) in %sFirstParty.php:%d
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation)
Test Finished (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest::testSelfDeprecation)
Test Suite Finished (PHPUnit\TestFixture\FailOnDeprecationPrecedence\SelfDeprecationTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit-fail-on-self-deprecation-false.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
