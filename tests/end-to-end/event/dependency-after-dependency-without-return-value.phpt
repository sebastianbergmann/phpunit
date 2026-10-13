--TEST--
The right events are emitted in the right order for tests that depend on a test that does not return a value and on another test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependencyAfterDependencyWithoutReturnValueTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest, 5 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testTwo)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testThree)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testThree)
Test Failed (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testThree)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testFour)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testFour)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testFour)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testFour)
Test Skipped (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testFive)
This test depends on "PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest::testThree" to pass
Test Suite Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyWithoutReturnValueTest, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
