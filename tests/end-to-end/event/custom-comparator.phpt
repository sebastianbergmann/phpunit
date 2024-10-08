--TEST--
The right events are emitted in the right order for a successful test that uses assertEquals() with a custom comparator
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/CustomComparator.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/CustomComparatorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sCustomComparator.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\CustomComparatorTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\CustomComparatorTest::testWithCustomComparator)
Test Prepared (PHPUnit\TestFixture\Event\CustomComparatorTest::testWithCustomComparator)
Comparator Registered (PHPUnit\TestFixture\Event\CustomComparator)
Test Passed (PHPUnit\TestFixture\Event\CustomComparatorTest::testWithCustomComparator)
Test Finished (PHPUnit\TestFixture\Event\CustomComparatorTest::testWithCustomComparator)
Test Suite Finished (PHPUnit\TestFixture\Event\CustomComparatorTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
