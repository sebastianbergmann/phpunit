--TEST--
Deprecations triggered at file scope are classified correctly with source configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/file-scope-deprecation-with-source';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Runner Triggered PHP Deprecation (issue triggered by first-party code calling into PHP runtime) in %sDeprecatedFunction.php:%d
Function utf8_encode() is deprecated since 8.2, visit the php.net documentation for various alternatives
Test Runner Triggered Deprecation (%s) in %sDeprecationTest.php:%d
file scope user deprecation
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource\DeprecationTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
