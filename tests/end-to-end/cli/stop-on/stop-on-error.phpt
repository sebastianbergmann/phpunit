--TEST--
Stopping test execution after first error works
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--stop-on-error';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/ErrorTest.php';

require __DIR__ . '/../../../bootstrap.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Errored (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
