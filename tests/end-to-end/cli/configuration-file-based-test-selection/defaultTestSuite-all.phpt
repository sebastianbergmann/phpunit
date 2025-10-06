--TEST--
Tests selected using <phpunit defaultTestSuite="<test suite>"> with "--all" CLI option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/configuration-file-based-test-selection/defaultTestSuite';
$_SERVER['argv'][] = '--all';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (unit, 1 test)
Test Suite Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest::testOne)
Test Prepared (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest::testOne)
Test Passed (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest::testOne)
Test Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\UnitTest, 1 test)
Test Suite Finished (unit, 1 test)
Test Suite Started (end-to-end, 1 test)
Test Suite Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest::testOne)
Test Prepared (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest::testOne)
Test Passed (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest::testOne)
Test Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\DefaultTestSuite\EndToEndTest, 1 test)
Test Suite Finished (end-to-end, 1 test)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
