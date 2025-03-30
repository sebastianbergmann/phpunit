--TEST--
The right events are emitted in the right order for a test that registers a failure interface
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/custom-failure-interface/bootstrap.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/custom-failure-interface/CustomFailureInterfaceTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sbootstrap.php)
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testOne)
this should be treated as a failure
Test Finished (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testTwo)
Test Errored (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testTwo)
this should be treated as an error
Test Finished (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Event\CustomFailureInterfaceTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
