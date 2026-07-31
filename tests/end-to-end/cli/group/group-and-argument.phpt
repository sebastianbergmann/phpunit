--TEST--
phpunit --group X+Y tests/MultiGroupTest.php (AND group filter)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'X+Y';
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
Test Preparation Started (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXY)
Test Prepared (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXY)
Test Passed (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXY)
Test Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXY)
Test Preparation Started (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXYZ)
Test Prepared (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXYZ)
Test Passed (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXYZ)
Test Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest::testXYZ)
Test Suite Finished (PHPUnit\TestFixture\GroupsWithAndLogic\MultiGroupTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
