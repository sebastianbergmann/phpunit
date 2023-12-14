--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5532
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/IgnoreDeprecationsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
message
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Triggered Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
message
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Assertion Succeeded (Constraint: is null, Value: null)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
message
Assertion Succeeded (Constraint: is of type array, Value: Array &0 [
    'type' => 16384,
    'message' => 'message',
    'file' => '%s%e_files%eIgnoreDeprecationsTest.php',
    'line' => %d,
])
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast)
Assertion Succeeded (Constraint: is null, Value: null)
Test Triggered Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast)
message
Assertion Succeeded (Constraint: is of type array, Value: Array &0 [
    'type' => 16384,
    'message' => 'message',
    'file' => '%s%e_files%eIgnoreDeprecationsTest.php',
    'line' => %d,
])
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
