--TEST--
phpunit --exclude-group one+two tests/BarTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--exclude-group';
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
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\GroupConjunctions\BarTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOne)
Test Passed (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\GroupConjunctions\BarTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\GroupConjunctions\BarTest::testThree)
Test Prepared (PHPUnit\TestFixture\GroupConjunctions\BarTest::testThree)
Test Passed (PHPUnit\TestFixture\GroupConjunctions\BarTest::testThree)
Test Finished (PHPUnit\TestFixture\GroupConjunctions\BarTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\GroupConjunctions\BarTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
