--TEST--
The right events are emitted in the right order for a test that registers a failure interface
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/CustomFailureInterfaceTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
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
