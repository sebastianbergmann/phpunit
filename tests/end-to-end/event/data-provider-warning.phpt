--TEST--
The right events are emitted in the right order for a successful test that uses a data provider which triggers E_USER_WARNING
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderWarningTest::values for test method PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess)
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderWarningTest::values for test method PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess)
Data Provider Triggered Warning (PHPUnit\TestFixture\Event\DataProviderWarningTest::values)
message
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderWarningTest::values
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderWarningTest::values
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderWarningTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#0)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#0)
Test Passed (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#0)
Test Finished (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#0)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#1)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#1)
Test Passed (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#1)
Test Finished (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess#1)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderWarningTest::testSuccess, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderWarningTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
