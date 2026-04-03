--TEST--
The right events are emitted in the right order for a test that uses backup globals with an exclude list
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/BackupGlobalsWithExcludeListTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testTwo)
Test Passed (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Event\BackupGlobalsWithExcludeListTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
