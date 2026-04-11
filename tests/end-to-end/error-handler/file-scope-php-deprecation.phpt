--TEST--
E_DEPRECATED triggered at file scope emits Test Runner Triggered PHP Deprecation event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FileScopePhpDeprecationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHP Deprecation (%s) in %sFileScopePhpDeprecationTest.php:%d
Function utf8_encode() is deprecated since 8.2, visit the php.net documentation for various alternatives
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\FileScopePhpDeprecationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
