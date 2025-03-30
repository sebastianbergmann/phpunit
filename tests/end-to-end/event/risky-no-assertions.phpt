--TEST--
The right events are emitted in the right order for a test that is considered risky because it did not perform assertions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyBecauseNoAssertionsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest::testOne)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseNoAssertionsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
