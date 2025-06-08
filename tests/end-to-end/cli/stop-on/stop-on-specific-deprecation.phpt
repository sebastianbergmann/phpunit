--TEST--
Stopping test execution after first deprecation where its message contains a given string
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-deprecation=bar';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SpecificDeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testOne, unknown if issue was triggered in first-party code or third-party code) in %s:%d
...foo...
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testTwo)
Test Triggered Deprecation (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testTwo, unknown if issue was triggered in first-party code or third-party code) in %s:%d
...bar...
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SpecificDeprecationTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
