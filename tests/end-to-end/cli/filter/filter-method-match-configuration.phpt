--TEST--
phpunit --filter testOne
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (%s%etests%eend-to-end%e_files%egroups%ephpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s%etests%eend-to-end%e_files%egroups%ephpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
