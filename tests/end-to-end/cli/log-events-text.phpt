--TEST--
phpunit --no-output --log-events-text logfile.txt
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (7 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (7 tests)
Test Suite Started (CLI Arguments, 7 tests)
Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Assertion Succeeded (Constraint: is null, Value: null)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Assertion Succeeded (Constraint: is identical to 1, Value: 1)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Assertion Succeeded (Constraint: is identical to 'hello, world!', Value: 'hello, world!')
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Assertion Succeeded (Constraint: is identical to Array &0 [
    0 => 1,
    'foo' => 2,
], Value: Array &0 [
    0 => 1,
    'foo' => 2,
])
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Assertion Failed (Constraint: is identical to an object of class "stdClass", Value: {enable export of objects to see this value})
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Failed asserting that two variables reference the same object.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Assertion Failed (Constraint: is identical to {enable export of objects to see this value}, Value: {enable export of objects to see this value})
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Failed asserting that two variables reference the same resource.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
Test Suite Finished (CLI Arguments, 7 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
