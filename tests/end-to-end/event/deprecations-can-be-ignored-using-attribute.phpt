--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5532
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/IgnoreDeprecationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
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
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne) in %s:%d
message
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Triggered Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo) in %s:%d
message
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast)
Assertion Succeeded (Constraint: is null, Value: null)
Test Triggered Test-Ignored Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testOneErrorGetLast) in %s:%d
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
Test Triggered Deprecation (PHPUnit\TestFixture\Event\IgnoreDeprecationsTest::testTwoErrorGetLast) in %s:%d
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
