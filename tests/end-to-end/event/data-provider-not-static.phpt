--TEST--
The right events are emitted in the right order for a test that uses a data provider that is not static
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/DynamicDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Data Provider Method Called (PHPUnit\TestFixture\Event\DynamicDataProviderTest::values for test method PHPUnit\TestFixture\Event\DynamicDataProviderTest::testSuccess)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DynamicDataProviderTest::testSuccess:
- PHPUnit\TestFixture\Event\DynamicDataProviderTest::values
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\DynamicDataProviderTest::testSuccess)
The data provider specified for PHPUnit\TestFixture\Event\DynamicDataProviderTest::testSuccess is invalid
Data Provider method PHPUnit\TestFixture\Event\DynamicDataProviderTest::values() is not static
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Event\DynamicDataProviderTest".)
Test Suite Loaded (0 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
