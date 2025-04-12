--TEST--
phpunit --debug --with-telemetry ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--with-telemetry';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Started (PHPUnit %s using %s)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Configured
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Event Facade Sealed
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Loaded (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Started
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Sorted
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Started (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (PHPUnit\TestFixture\Basic\SuccessTest, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\Basic\SuccessTest::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\Basic\SuccessTest::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Passed (PHPUnit\TestFixture\Basic\SuccessTest::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\Basic\SuccessTest::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (PHPUnit\TestFixture\Basic\SuccessTest, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Finished (Shell Exit Code: 0)
