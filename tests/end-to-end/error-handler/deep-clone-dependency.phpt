--TEST--
Issues triggered while the return value of a depended-upon test is deep-cloned are handled by the previous error handler
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DeepCloneDependencyTest.php';

require __DIR__ . '/../../bootstrap.php';

set_error_handler(static function (int $errno, string $errstr): bool {
    print 'previous error handler: ' . $errstr . PHP_EOL;

    return true;
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testProducer)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testProducer)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testProducer)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testProducer)
previous error handler: notice from __clone()
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testConsumer)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testConsumer)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testConsumer)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest::testConsumer)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeepCloneDependencyTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
