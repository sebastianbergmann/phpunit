--TEST--
phpunit --parallel=2 runs an in-process unit that leads a later chunk inside that chunk's test-suite envelope, not inside the envelope of the chunk that finished before it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using PHP %s (%s) on %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Child Process Started (worker for parallel test execution)
Child Process Started (worker for parallel test execution)
Test Suite Started (%sphpunit.xml, 3 tests)
Test Suite Started (one, 1 test)
Test Suite Started (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest::testThatPasses)
Test Prepared (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest::testThatPasses)
Test Passed (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest::testThatPasses)
Test Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest::testThatPasses)
Test Suite Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerOneTest, 1 test)
Test Suite Finished (one, 1 test)
Test Suite Started (two, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest::testThatPasses)
Test Prepared (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest::testThatPasses)
Test Passed (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest::testThatPasses)
Test Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest::testThatPasses)
Test Suite Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\MainProcessTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest::testThatPasses)
Test Prepared (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest::testThatPasses)
Test Passed (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest::testThatPasses)
Test Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest::testThatPasses)
Test Suite Finished (PHPUnit\TestFixture\ParallelChunkBoundaries\WorkerTwoTest, 1 test)
Test Suite Finished (two, 2 tests)
Child Process Finished (worker for parallel test execution)
Child Process Finished (worker for parallel test execution)
Test Suite Finished (%sphpunit.xml, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
