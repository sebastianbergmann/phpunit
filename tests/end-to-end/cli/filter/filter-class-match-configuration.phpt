--TEST--
phpunit --filter FooTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'FooTest';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (3 tests)
Test Runner Execution Started (3 tests)
Test Suite Started (%s%etests%eend-to-end%e_files%egroups%ephpunit.xml, 3 tests)
Test Suite Started (default, 3 tests)
Test Suite Started (PHPUnit\TestFixture\Groups\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\Groups\FooTest, 3 tests)
Test Suite Finished (default, 3 tests)
Test Suite Finished (%s%etests%eend-to-end%e_files%egroups%ephpunit.xml, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
