--TEST--
E_WARNING triggered by PHP runtime emits Test Triggered PHP Warning event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest::testPhpWarning)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest::testPhpWarning)
Test Triggered PHP Warning (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest::testPhpWarning) in %s:%d
file_get_contents(%s): Failed to open stream: No such file or directory
Test Passed (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest::testPhpWarning)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest::testPhpWarning)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PhpWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
