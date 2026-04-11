--TEST--
E_WARNING triggered at file scope emits Test Runner Triggered PHP Warning event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FileScopePhpWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHP Warning () in %sFileScopePhpWarningTest.php:%d
file_get_contents(/nonexistent/file/for/phpunit/test): Failed to open stream: No such file or directory
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\FileScopePhpWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
