--TEST--
No further examples of a property are generated once every example that its generators can generate has been generated, and the property is only considered risky when all of them were rejected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExhaustedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest, 4 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples, seed: %d, examples: 2, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasTwoExamples, 100 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples, seed: %d, examples: 6, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasSixExamples, 100 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample, seed: %d, examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testHasOneExample, 100 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample)
Test Considered Risky (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample)
This property was checked for only 0 of 100 examples, 2 examples were rejected by assume(); generate the values that the property needs instead of rejecting the others
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample, seed: %d, examples: 0, rejected: 2)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest::testRejectsEveryExample, 100 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExhaustedExamplesTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
