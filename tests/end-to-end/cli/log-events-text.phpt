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
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (CLI Arguments, 2 tests)
Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Failed asserting that two variables reference the same object.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportObject)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Passed (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testExportNull)
Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 2 tests)
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
