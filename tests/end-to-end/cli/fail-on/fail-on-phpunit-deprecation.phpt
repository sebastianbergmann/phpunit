--TEST--
Test Runner exits with shell exit code indicating failure when all tests are successful but at least one test triggered a PHPUnit deprecation
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--fail-on-phpunit-deprecation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/DeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne, unknown if issue was triggered in first-party code or third-party code)
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testThree)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testThree)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testThree)
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testThree)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\DeprecationTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
