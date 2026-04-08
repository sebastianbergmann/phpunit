--TEST--
phpunit --run-test-id 'PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100)'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--run-test-id';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100)';
$_SERVER['argv'][] = __DIR__ . '/../../_files/test-id-filter/tests/BarTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\TestIdFilter\BarTest::provideData for test method PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters)
Data Provider Method Finished for PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters:
- PHPUnit\TestFixture\TestIdFilter\BarTest::provideData
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\TestIdFilter\BarTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100))
Test Prepared (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100))
Test Passed (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100))
Test Finished (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters#total ($100))
Test Suite Finished (PHPUnit\TestFixture\TestIdFilter\BarTest::testWithSpecialCharacters, 1 test)
Test Suite Finished (PHPUnit\TestFixture\TestIdFilter\BarTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
