--TEST--
The right events are emitted in the right order for a test that has a missing dependency
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/MissingDependencyTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\MissingDependencyTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\MissingDependencyTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\MissingDependencyTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\MissingDependencyTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Event\MissingDependencyTest::testOne)
Test Skipped (PHPUnit\TestFixture\Event\MissingDependencyTest::testTwo)
This test depends on "PHPUnit\TestFixture\Event\MissingDependencyTest::testOne" to pass
Test Suite Finished (PHPUnit\TestFixture\Event\MissingDependencyTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
