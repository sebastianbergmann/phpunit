--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5838
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InheritedSeparateProcessesTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest, 1 test)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest::testOne)
Test Prepared (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest::testOne)
Test Passed (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest::testOne)
Test Finished (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest::testOne)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Issue5838\InheritedSeparateProcessesTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
