--TEST--
Stopping test execution after first skipped test works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--skip-covers-nothing';
$_SERVER['argv'][] = __DIR__ . '/../../_files/skip-covers-nothing/SkippedTestWithAttribute.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithAttribute)
Test Prepared (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithAttribute)
Test Passed (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithAttribute)
Test Skipped (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithAttribute)
This test skipped because of --skip-covers-nothing flag: 
Test Finished (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithAttribute)
Test Preparation Started (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithoutAttribute)
Test Prepared (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithoutAttribute)
Test Passed (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithoutAttribute)
Test Finished (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute::testCoversNothingWithoutAttribute)
Test Suite Finished (PHPUnit\TestFixture\SkipCoversNothing\SkippedTestWithAttribute, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
