--TEST--
The right events are emitted in the right order for tests that use dependency return value cloning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependencyReturnValueCloningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testProducer)
Test Prepared (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testProducer)
Test Passed (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testProducer)
Test Finished (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testProducer)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDeepCloneConsumer)
Test Prepared (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDeepCloneConsumer)
Test Passed (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDeepCloneConsumer)
Test Finished (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDeepCloneConsumer)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testShallowCloneConsumer)
Test Prepared (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testShallowCloneConsumer)
Test Passed (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testShallowCloneConsumer)
Test Finished (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testShallowCloneConsumer)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDirectConsumer)
Test Prepared (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDirectConsumer)
Test Passed (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDirectConsumer)
Test Finished (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest::testDirectConsumer)
Test Suite Finished (PHPUnit\TestFixture\Event\DependencyReturnValueCloningTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
