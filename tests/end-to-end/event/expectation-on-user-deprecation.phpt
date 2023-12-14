--TEST--
The right events are emitted in the right order for a test which expects the tested code to trigger E_USER_DEPRECATED issues
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/TestForDeprecatedFeatureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (4 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testOne)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testOne)
message
Test Passed (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo)
something else
Test Failed (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo)
Expected deprecation with message "message" was not triggered
Test Finished (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testThree)
Test Prepared (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testThree)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testThree)
...message...
Test Passed (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testThree)
Test Finished (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour)
Test Prepared (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour)
something else
Test Failed (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour)
Expected deprecation with message "message" was not triggered
Test Finished (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour)
Test Suite Finished (PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
