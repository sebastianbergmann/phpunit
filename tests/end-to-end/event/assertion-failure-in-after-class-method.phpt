--TEST--
The right events are emitted in the right order for a test that fails because of an assertion failure in a "after last test" method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInTearDownAfterClassTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::tearDownAfterClass)
After Last Test Method Failed (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::tearDownAfterClass)
Failed asserting that false is true.
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::tearDownAfterClass
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
