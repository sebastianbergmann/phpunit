--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5287
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/5287/C/MyClassTest.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5287';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sMyClassTest.php)
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::provide for test method PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test)
Data Provider Method Finished for PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test:
- PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::provide
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Prepared (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Passed (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\B\MyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Test Prepared (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Test Failed (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\B\MyClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\C\MyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Prepared (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Passed (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Finished (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\C\MyClassTest, 1 test)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
