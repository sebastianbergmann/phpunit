--TEST--
The Test\PropertyChecked event has the statistics of the values that the examples of a property that were checked observed with each label, without the examples that were rejected and the explicit examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ObservedValuesTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples, seed: %d, examples: 9, rejected: 1)
value: count 9, min 0, median 4, mean 4, p90 8, max 8
third: count 9, min 0, median 1.33333, mean 1.33333, p90 2.66667, max 2.66667
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
