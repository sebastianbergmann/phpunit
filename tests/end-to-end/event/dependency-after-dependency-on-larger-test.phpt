--TEST--
The right events are emitted in the right order for tests that depend on a larger test and on another test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/dependency-after-dependency-on-larger-test';

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
Test Suite Started (CLI Arguments, 5 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\LargeTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testTwo)
Test Failed (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testTwo)
Test Considered Risky (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testThree)
This test depends on a test that is larger than itself
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testThree)
Test Prepared (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testThree)
Test Passed (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testThree)
Test Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testThree)
Test Skipped (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testFour)
This test depends on "PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest::testTwo" to pass
Test Suite Finished (PHPUnit\TestFixture\Event\DependencyAfterDependencyOnLargerTest\SmallTest, 4 tests)
Test Suite Finished (CLI Arguments, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
