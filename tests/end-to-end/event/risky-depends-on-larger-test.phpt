--TEST--
The right events are emitted in the right order for a test that is considered risky because it depends on a larger test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/test-risky-depends-on-larger-test';

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
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\LargeTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest, 1 test)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest::testOne)
This test depends on a test that is larger than itself
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseDependencyOnLargerTest\SmallTest, 1 test)
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
