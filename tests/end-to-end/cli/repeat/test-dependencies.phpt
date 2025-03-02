--TEST--
phpunit --repeat 2 ../../_files/repeat/single-test-class-that-uses-test-dependencies/tests/TestDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/repeat/single-test-class-that-uses-test-dependencies/tests/TestDependencyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\TestDependencyTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\TestDependencyTest::testOne)
Test Prepared (PHPUnit\TestFixture\Repeat\TestDependencyTest::testOne)
Test Passed (PHPUnit\TestFixture\Repeat\TestDependencyTest::testOne)
Test Finished (PHPUnit\TestFixture\Repeat\TestDependencyTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Repeat\TestDependencyTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Repeat\TestDependencyTest::testTwo)
Test Passed (PHPUnit\TestFixture\Repeat\TestDependencyTest::testTwo)
Test Finished (PHPUnit\TestFixture\Repeat\TestDependencyTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Repeat\TestDependencyTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
