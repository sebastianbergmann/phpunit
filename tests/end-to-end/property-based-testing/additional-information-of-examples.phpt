--TEST--
The additional information that the examples of a property provide is reported only for the example whose outcome is reported: the minimal counterexample, or the explicit example that falsified the property, and not for a property that passed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/AdditionalInformationOfExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest, 3 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen)
Test Provided Additional Information
sum of 0 and 11
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen, seed: %d, examples: %d, shrinking steps: %d, shrinking attempts: %d)
$a = 0;
$b = 11;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen)
Failed asserting that 11 is equal to 10 or is less than 10.
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen, seed: %d, examples: %d, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testSumIsAtMostTen, 100 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative, 3 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative)
Test Provided Additional Information
value -1
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative, explicit example #1)
$value = -1;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative)
Failed asserting that -1 is equal to 0 or is greater than 0.
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative, seed: %d, examples: 0, explicit examples: 2, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testValueIsNotNegative, 3 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds, 3 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds, seed: %d, examples: 3, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest::testHolds, 3 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\AdditionalInformationOfExamplesTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
