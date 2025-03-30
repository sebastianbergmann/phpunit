--TEST--
The right events are emitted in the right order when too few columns are requested
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--columns';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered Warning (Less than 16 columns requested, number of columns set to 16)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Passed (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Finished (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
