--TEST--
The right events are emitted in the right order for a test that depends on a test class that was skipped
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/depends-on-skipped-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (CLI Arguments, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest::setUpBeforeClass)
Before First Test Method Finished:
- PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest::setUpBeforeClass
Test Suite Skipped (PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest, message)
Test Suite Finished (PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DependsOnSkippedClass\DependentTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DependsOnSkippedClass\DependentTest::testOne)
Test Skipped (PHPUnit\TestFixture\Event\DependsOnSkippedClass\DependentTest::testOne)
This test depends on "PHPUnit\TestFixture\Event\DependsOnSkippedClass\SkippedTest::class" to pass
Test Finished (PHPUnit\TestFixture\Event\DependsOnSkippedClass\DependentTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\DependsOnSkippedClass\DependentTest, 1 test)
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
