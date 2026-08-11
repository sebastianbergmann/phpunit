--TEST--
Issues triggered while the return value of a depended-upon test is deep-cloned are not suppressed when no previous error handler is registered
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DeepCloneDependencyWithoutPreviousErrorHandlerTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testProducer)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testProducer)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testProducer)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testProducer)

Notice: notice from __clone() in %s on line %d
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testConsumer)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testConsumer)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testConsumer)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest::testConsumer)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyWithoutPreviousErrorHandlerTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
