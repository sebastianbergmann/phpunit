--TEST--
--property-maximum-shrinking-attempts 0 reports the counterexample of a falsified property without shrinking it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--property-maximum-shrinking-attempts';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = __DIR__ . '/_files/FalsifiedPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen)
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen, seed: %d, examples: 1, shrinking steps: 0, shrinking attempts: 0)
$a = 13;
$b = 0;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen)
Failed asserting that 13 is equal to 10 or is less than 10.

Property falsified after 1 example with this counterexample:

    $a = 13;
    $b = 0;
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen, seed: %d, examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen, 100 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
