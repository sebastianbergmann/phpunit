--TEST--
Configured deprecation trigger method filters stack trace and guesses frame
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger-method';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest::testDeprecationViaMethod)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest::testDeprecationViaMethod)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest::testDeprecationViaMethod, issue triggered by first-party code calling into third-party code, suppressed using operator) in %s:%d
deprecation via method trigger
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest::testDeprecationViaMethod)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest::testDeprecationViaMethod)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerMethod\DeprecationTriggerTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
