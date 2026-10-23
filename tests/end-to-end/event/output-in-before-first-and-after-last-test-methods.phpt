--TEST--
The right events are emitted in the right order for output that is printed in setUpBeforeClass() and tearDownAfterClass()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/OutputInBeforeFirstAndAfterLastTestMethodsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::setUpBeforeClass)
output from setUpBeforeClass()
Before First Test Method Finished:
- PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::setUpBeforeClass
Test Preparation Started (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::tearDownAfterClass)
output from tearDownAfterClass()
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest::tearDownAfterClass
Test Suite Finished (PHPUnit\TestFixture\Event\OutputInBeforeFirstAndAfterLastTestMethodsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
