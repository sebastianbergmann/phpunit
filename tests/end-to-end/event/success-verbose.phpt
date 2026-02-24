--TEST--
The right events are emitted in the right order for a successful test with telemetry information
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--with-telemetry';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Started (PHPUnit %s using %s)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Configured
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Event Facade Sealed
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Loaded (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Started
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Sorted
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Started (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Finished (Shell Exit Code: 0)
