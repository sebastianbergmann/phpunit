--TEST--
A previously registered error handler that checks error_reporting() dynamically observes the suppression mask for an error suppressed using the @ operator in a data provider
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderSuppressedTest.php';

require __DIR__ . '/../../bootstrap.php';

error_reporting(E_ALL);

set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    if (($errorNumber & error_reporting()) === 0) {
        return false;
    }

    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::provider for test method PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider:
- PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider, 1 test)
Test Triggered Warning (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0) in %s:%d
suppressed warning from data provider
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider, 1 test)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
