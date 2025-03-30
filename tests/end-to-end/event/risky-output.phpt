--TEST--
The right events are emitted in the right order for a test that is considered risky because it prints output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyBecauseOutputTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest::testOne)
Test Printed Unexpected Output
*
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest::testOne)
Test code or tested code printed unexpected output: *
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseOutputTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
