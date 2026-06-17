--TEST--
#[Retry] forwards the events of an attempt unchanged when its outcome cannot be determined from them
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UndeterminableAttemptOutcomeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne, up to 3 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne)
After Test Method Called (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::tearDown)
After Test Method Finished:
- PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::tearDown
Test Passed (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\UndeterminableAttemptOutcomeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
