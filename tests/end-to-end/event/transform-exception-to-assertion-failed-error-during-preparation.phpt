--TEST--
The right events are emitted in the right order for a test that transforms an exception to AssertionFailedError during preparation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/TransformExceptionToAssertionFailedErrorDuringPreparationTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::setUp)
Before Test Method Errored (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::setUp)
setup failed
Before Test Method Finished:
- PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::setUp
Test Preparation Failed (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::testOne)
setup failed
Test Errored (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::testOne)
setup failed
Test Considered Risky (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest::testOne)
This test did not perform any assertions
Test Suite Finished (PHPUnit\TestFixture\Event\TransformExceptionToAssertionFailedErrorDuringPreparationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
