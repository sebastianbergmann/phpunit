--TEST--
Custom IssueTriggerResolver that returns null defers to next resolver in chain
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/issue-trigger-resolver-skipped';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest::testDeprecationWithSkippingResolver)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest::testDeprecationWithSkippingResolver)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest::testDeprecationWithSkippingResolver, issue triggered by first-party code calling into third-party code, suppressed using operator) in %s:%d
third-party deprecation
Test Passed (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest::testDeprecationWithSkippingResolver)
Test Finished (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest::testDeprecationWithSkippingResolver)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\IssueTriggerResolverSkipped\IssueTriggerResolverSkippedTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
