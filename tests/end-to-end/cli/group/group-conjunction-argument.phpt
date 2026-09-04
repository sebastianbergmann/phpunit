--TEST--
phpunit --group one+two tests/BarTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one+two';
$_SERVER['argv'][] = __DIR__ . '/../../_files/group-conjunctions/tests/BarTest.php';

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
Test Suite Started (PHPUnit\TestFixture\GroupConjunctions\BarTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOneAndTwo)
Test Prepared (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOneAndTwo)
Test Passed (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOneAndTwo)
Test Finished (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOneAndTwo)
Test Suite Finished (PHPUnit\TestFixture\GroupConjunctions\BarTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
