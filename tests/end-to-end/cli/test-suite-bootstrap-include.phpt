--TEST--
Bootstrap script specific to test suite is not loaded when the test suite is not selected using --testsuite
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/bootstrap-for-test-suite/phpunit.xml';
$_SERVER['argv'][] = '--testsuite';
$_SERVER['argv'][] = 'one';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s)
Test Runner Configured
Bootstrap Finished (%stests/bootstrap/bootstrap.php)
Bootstrap Finished (%stests/bootstrap/bootstrap_one.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (one, 1 test)
Test Suite Started (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Prepared (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Passed (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Finished (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\BootstrapForTestSuite\OneTest, 1 test)
Test Suite Finished (one, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
