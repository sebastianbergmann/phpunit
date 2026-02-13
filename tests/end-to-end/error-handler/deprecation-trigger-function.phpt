--TEST--
Configured deprecation trigger function filters stack trace and guesses frame
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger-function';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest::testDeprecationViaFunction)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest::testDeprecationViaFunction)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest::testDeprecationViaFunction, issue triggered by first-party code calling into third-party code, suppressed using operator) in %s:%d
deprecation via function trigger
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest::testDeprecationViaFunction)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest::testDeprecationViaFunction)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerFunction\DeprecationTriggerTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
