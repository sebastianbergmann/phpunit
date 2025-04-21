--TEST--
The right events are emitted in the right order for a test that fails because of an assertion failure in a "before first test" method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInSetUpBeforeClassTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInSetUpBeforeClassTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\Event\AssertionFailureInSetUpBeforeClassTest::setUpBeforeClass)
Before First Test Method Failed (PHPUnit\TestFixture\Event\AssertionFailureInSetUpBeforeClassTest::setUpBeforeClass)
Failed asserting that false is true.
Before First Test Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInSetUpBeforeClassTest::setUpBeforeClass
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInSetUpBeforeClassTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
