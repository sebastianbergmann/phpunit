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
Event Facade Sealed
Test Suite Loaded (7 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (7 tests)
Test Suite Started (CLI Arguments, 7 tests)
Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Failed asserting that two variables reference the same object.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Failed asserting that two variables reference the same resource.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
Test Suite Finished (CLI Arguments, 7 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
