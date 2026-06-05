--TEST--
deferToPreviousErrorHandler="true" also applies to issues triggered outside of tests (e.g. at file scope)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/defer-to-previous-error-handler-file-scope';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Runner Triggered PHPUnit Notice (%s)
Test Runner Triggered Deprecation (unknown if issue was triggered in first-party code or third-party code) in %sDeferToPreviousErrorHandlerFileScopeTest.php:%d
report this deprecation at file scope
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest::testOne)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest::testOne)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest::testOne)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeferToPreviousErrorHandlerFileScope\DeferToPreviousErrorHandlerFileScopeTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
