--TEST--
The right events are emitted in the right order for an incomplete test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/IncompleteTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\IncompleteTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\IncompleteTest::testIncomplete)
Test Prepared (PHPUnit\TestFixture\Event\IncompleteTest::testIncomplete)
Test Marked Incomplete (PHPUnit\TestFixture\Event\IncompleteTest::testIncomplete)
message
Test Finished (PHPUnit\TestFixture\Event\IncompleteTest::testIncomplete)
Test Suite Finished (PHPUnit\TestFixture\Event\IncompleteTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
