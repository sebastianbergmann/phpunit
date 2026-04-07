--TEST--
phpunit --test-id-filter-file test-ids.txt tests/FooTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--test-id-filter-file';
$_SERVER['argv'][] = __DIR__ . '/../../_files/test-id-filter/test-ids.txt';
$_SERVER['argv'][] = __DIR__ . '/../../_files/test-id-filter/tests/FooTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\TestIdFilter\FooTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestIdFilter\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestIdFilter\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\TestIdFilter\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\TestIdFilter\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestIdFilter\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\TestIdFilter\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\TestIdFilter\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\TestIdFilter\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\TestIdFilter\FooTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
