--TEST--
The right events are emitted in the right order for when an exception is raised in tearDownAfterClass()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExceptionInTearDownAfterClassTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::tearDownAfterClass)
After Last Test Method Errored (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::tearDownAfterClass)
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest::tearDownAfterClass
Test Suite Finished (PHPUnit\TestFixture\Event\ExceptionInTearDownAfterClassTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
