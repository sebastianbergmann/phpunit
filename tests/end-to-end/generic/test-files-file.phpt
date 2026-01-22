--TEST--
phpunit --test-files-file test_files.txt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file';
$_SERVER['argv'][] = '--test-files-file';
$_SERVER['argv'][] = __DIR__ . '/_files/test-files-file/test_files.txt';
$_SERVER['argv'][] = '--debug';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (CLI Arguments, 2 tests)
Test Suite Started (PHPUnit\TestFixture\TestFilesFile\OneTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestFilesFile\OneTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestFilesFile\OneTest::testOne)
Test Passed (PHPUnit\TestFixture\TestFilesFile\OneTest::testOne)
Test Finished (PHPUnit\TestFixture\TestFilesFile\OneTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TestFilesFile\OneTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\TestFilesFile\ThreeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestFilesFile\ThreeTest::testThree)
Test Prepared (PHPUnit\TestFixture\TestFilesFile\ThreeTest::testThree)
Test Passed (PHPUnit\TestFixture\TestFilesFile\ThreeTest::testThree)
Test Finished (PHPUnit\TestFixture\TestFilesFile\ThreeTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\TestFilesFile\ThreeTest, 1 test)
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
