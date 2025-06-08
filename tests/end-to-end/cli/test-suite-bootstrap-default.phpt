--TEST--
All bootstrap scripts are loaded by default
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/bootstrap-for-test-suite/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s)
Test Runner Configured
Bootstrap Finished (%sbootstrap.php)
Bootstrap Finished (%sbootstrap_one.php)
Bootstrap Finished (%sbootstrap_two.php)
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (one, 1 test)
Test Suite Started (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Prepared (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Passed (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Finished (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest, 1 test)
Test Suite Finished (one, 1 test)
Test Suite Started (two, 1 test)
Test Suite Started (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest::testTwo)
Test Prepared (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest::testTwo)
Test Passed (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest::testTwo)
Test Finished (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\BootstrapForTestSuite\TwoTest, 1 test)
Test Suite Finished (two, 1 test)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
