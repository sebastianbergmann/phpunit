--TEST--
Test Runner exits with dedicated shell exit code when it is requested and a before-first-test method errored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--dedicated-exit-code-for-failed-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/ExceptionInBeforeClassMethodTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest::setUpBeforeClass)
Before First Test Method Errored (PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest::setUpBeforeClass)
message
Before First Test Method Finished:
- PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest::setUpBeforeClass
Test Suite Finished (PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 3)
