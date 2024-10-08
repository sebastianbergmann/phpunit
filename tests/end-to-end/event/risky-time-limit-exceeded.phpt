--TEST--
The right events are emitted in the right order for a test that is considered risky because it timed out
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyBecauseTimeLimitExceededTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest::testOne)
This test was aborted after 1 second
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseTimeLimitExceededTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
