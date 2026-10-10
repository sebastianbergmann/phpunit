--TEST--
The Test\PropertyChecked event states how many of the examples of a property that were checked were classified with each label, without the examples that were rejected and the explicit examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassifiedExamplesTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples, seed: %d, examples: 9, rejected: 1)
odd: 4 (44.4%)
even: 5 (55.6%)
zero: 1 (11.1%)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
