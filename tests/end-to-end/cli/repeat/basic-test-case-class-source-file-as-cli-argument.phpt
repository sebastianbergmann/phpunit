--TEST--
phpunit --repeat 2 ../../_files/repeat/single-test-class/tests/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/repeat/single-test-class/tests/SuccessTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\SuccessTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Repeat\SuccessTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
