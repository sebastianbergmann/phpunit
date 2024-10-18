--TEST--
The right events are emitted in the right order for a test that is run in process isolation and triggers a fatal error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FatalTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\FatalTest, 1 test)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Event\FatalTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\FatalTest::testOne)
Test Errored (PHPUnit\TestFixture\Event\FatalTest::testOne)
Call to undefined function PHPUnit\TestFixture\Event\doesNotExist()
Test Finished (PHPUnit\TestFixture\Event\FatalTest::testOne)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Event\FatalTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
