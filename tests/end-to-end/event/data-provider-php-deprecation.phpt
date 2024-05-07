--TEST--
The right events are emitted in the right order for a successful test that uses a data provider which triggers E_DEPRECATED
--XFAIL--
"Data Provider Triggered PHP Deprecation" event is not emitted
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderPhpDeprecationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::values for test method PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess)
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::values for test method PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess)
Data Provider Triggered PHP Deprecation (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::values, issue triggered by first-party code calling into first-party code)
strlen(): Passing null to parameter #1 ($string) of type string is deprecated
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::values
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::values
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#0)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#0)
Test Passed (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#0)
Test Finished (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#0)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#1)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#1)
Test Passed (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#1)
Test Finished (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess#1)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest::testSuccess, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderPhpDeprecationTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
