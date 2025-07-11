--TEST--
The right events are emitted in the right order for a successful test that uses the TestWith and TestWithJson attributes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/Metadata/Attribute/tests/TestWithTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (11 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (11 tests)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest, 11 tests)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName#Name1)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName#Name1)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName#Name1)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName#Name1)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOneWithName, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName#Name2)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName#Name2)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName#Name2)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName#Name2)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwoWithName, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayBasic, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase#firstCase)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase#firstCase)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase#firstCase)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase#firstCase)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayNamedCase, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#odds)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#odds)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#odds)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#odds)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#0)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#evens)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#evens)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#evens)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames#evens)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTestWithArrayManyCasesWithMixedNames, 3 tests)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#0)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#1)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#1)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#1)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray#1)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testMultipleTestWithArray, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest, 11 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
