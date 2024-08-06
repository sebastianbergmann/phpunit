--TEST--
phpunit --no-output --log-events-verbose-text logfile.txt
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-verbose-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Started (%s)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Configured
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Event Facade Sealed
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Loaded (7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Started
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Sorted
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Started (7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (CLI Arguments, 7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportBool)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportInt)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportStr)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportArray)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
                                                         %sFailed asserting that two variables reference the same object.
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
                                                         %sFailed asserting that two variables reference the same resource.
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportResource)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (CLI Arguments, 7 tests)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Finished (Shell Exit Code: 1)
