--TEST--
Custom IssueTriggerResolver that returns null defers to next resolver in chain for an issue triggered outside a test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/issue-trigger-resolver-skipped-file-scope';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Runner Triggered Deprecation (issue triggered by PHPUnit calling into third-party code) in %sFileScopeDeprecationTest.php:%d
file scope deprecation
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkippedFileScope\FileScopeDeprecationTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
