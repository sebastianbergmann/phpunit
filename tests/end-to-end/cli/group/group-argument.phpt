--TEST--
phpunit --group one tests/FooTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups/tests/FooTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
