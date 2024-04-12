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
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidParameterNameDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Data Provider Method Called (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::values for test method PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess)
Data Provider Method Finished for PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess:
- PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::values
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Providing invalid named argument $value3 for method PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess() is deprecated and will not be supported in PHPUnit 11.0.
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Providing invalid named argument $value4 for method PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess() is deprecated and will not be supported in PHPUnit 11.0.
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#0)
Test Prepared (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#0)
Assertion Succeeded (Constraint: is true, Value: true)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#0)
Test Finished (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#0)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Test Prepared (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Assertion Succeeded (Constraint: is true, Value: true)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Test Finished (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess#1)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest::testSuccess, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidParameterNameDataProviderTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
