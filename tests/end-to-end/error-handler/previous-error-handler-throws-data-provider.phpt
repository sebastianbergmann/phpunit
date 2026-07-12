--TEST--
A previously registered error handler that turns an error triggered in a data provider into an exception errors the test instead of aborting the test runner
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PreviousErrorHandlerThrowsDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

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
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::provider for test method PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider:
- PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider#0)
Test Preparation Errored (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider#0)
warning from data provider
Test Errored (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider#0)
warning from data provider
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest::testWarningFromDataProvider, 1 test)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsDataProviderTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
