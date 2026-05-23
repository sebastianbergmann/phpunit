--TEST--
The right events are emitted in the right order for a test that compares closures for equality
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ClosureComparisonTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\ClosureComparisonTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\ClosureComparisonTest::testClosureComparison)
Test Prepared (PHPUnit\TestFixture\Event\ClosureComparisonTest::testClosureComparison)
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\Event\ClosureComparisonTest::testClosureComparison)
Comparing closures for equality is problematic because there is no reliable way to determine whether two closures are equal
Test Passed (PHPUnit\TestFixture\Event\ClosureComparisonTest::testClosureComparison)
Test Finished (PHPUnit\TestFixture\Event\ClosureComparisonTest::testClosureComparison)
Test Suite Finished (PHPUnit\TestFixture\Event\ClosureComparisonTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
