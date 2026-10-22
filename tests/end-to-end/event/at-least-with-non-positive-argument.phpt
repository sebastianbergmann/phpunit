--TEST--
The right events are emitted in the right order for a test that calls atLeast() with a non-positive argument
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AtLeastWithNonPositiveArgumentTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest::testAtLeastWithZero)
Test Prepared (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest::testAtLeastWithZero)
Mock Object Created (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentInterface)
Test Errored (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest::testAtLeastWithZero)
The argument passed to atLeast() must be a positive integer, 0 given
Test Finished (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest::testAtLeastWithZero)
Test Suite Finished (PHPUnit\TestFixture\Event\AtLeastWithNonPositiveArgumentTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
