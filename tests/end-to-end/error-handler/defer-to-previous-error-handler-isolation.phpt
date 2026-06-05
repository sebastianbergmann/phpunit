--TEST--
deferToPreviousErrorHandler="true" also applies when tests are run in separate processes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/defer-to-previous-error-handler';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Runner Triggered PHPUnit Notice (%s)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest, 1 test)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest::testOne)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest::testOne, unknown if issue was triggered in first-party code or third-party code) in %s:%d
report this deprecation
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest::testOne)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest::testOne)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandler\DeferToPreviousErrorHandlerTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
