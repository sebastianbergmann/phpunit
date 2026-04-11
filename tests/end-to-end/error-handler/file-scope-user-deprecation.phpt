--TEST--
E_USER_DEPRECATED triggered at file scope emits Test Runner Triggered Deprecation event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FileScopeUserDeprecationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered Deprecation (%s) in %sFileScopeUserDeprecationTest.php:%d
file scope user deprecation
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeUserDeprecationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
