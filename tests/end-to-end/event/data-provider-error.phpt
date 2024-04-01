--TEST--
The right events are emitted in the right order for a successful test that uses a data provider which triggers E_USER_ERROR
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderErrorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderErrorTest::values for test method PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess)
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderErrorTest::values for test method PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess)
Data Provider Triggered Error (PHPUnit\TestFixture\Event\DataProviderErrorTest::values)
message
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderErrorTest::values
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderErrorTest::values
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess)
The data provider specified for PHPUnit\TestFixture\Event\DataProviderErrorTest::testSuccess is invalid
E_USER_ERROR was triggered
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Event\DataProviderErrorTest".)
Test Suite Loaded (0 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
