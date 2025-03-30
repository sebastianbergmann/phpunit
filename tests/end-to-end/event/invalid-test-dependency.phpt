--TEST--
The right events are emitted in the right order for a test that has an invalid dependency
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidDependencyTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\InvalidDependencyTest, 2 tests)
Test Errored (PHPUnit\TestFixture\Event\InvalidDependencyTest::testOne)
This test depends on "PHPUnit\TestFixture\Event\InvalidDependencyTest::doesNotExist" which does not exist
Test Errored (PHPUnit\TestFixture\Event\InvalidDependencyTest::testTwo)
This test depends on "DoesNotExist" which does not exist
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidDependencyTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
