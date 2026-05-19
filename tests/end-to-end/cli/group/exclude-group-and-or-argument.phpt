--TEST--
phpunit --exclude-group X+Y --exclude-group Z tests/MultiGroupTest.php (AND combined with OR exclude group filter)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'X+Y';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'Z';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups-with-and-logic/tests/MultiGroupTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testX)
Test Prepared (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testX)
Test Passed (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testX)
Test Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testX)
Test Preparation Started (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testY)
Test Prepared (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testY)
Test Passed (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testY)
Test Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testY)
Test Suite Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
