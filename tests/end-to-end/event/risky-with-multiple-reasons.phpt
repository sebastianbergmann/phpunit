--TEST--
The right events are emitted in the right order for a test that is considered risky for multiple reasons
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--globals-backup';
$_SERVER['argv'][] = '--strict-global-state';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyWithMultipleReasonsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
This test modified global state but was not expected to do so
--- Global variables before the test
+++ Global variables after the test
%A
+    'variable' => 'value',
%A
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyWithMultipleReasonsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
