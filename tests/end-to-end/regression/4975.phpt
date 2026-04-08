--TEST--
GH-4975: --filter does not work when filter string starts with #
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = '#2';
$_SERVER['argv'][] = __DIR__ . '/4975/Issue4975Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Issue4975Test::provider for test method PHPUnit\TestFixture\Issue4975Test::testSomething)
Data Provider Method Finished for PHPUnit\TestFixture\Issue4975Test::testSomething:
- PHPUnit\TestFixture\Issue4975Test::provider
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue4975Test, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue4975Test::testSomething, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue4975Test::testSomething##2 second)
Test Prepared (PHPUnit\TestFixture\Issue4975Test::testSomething##2 second)
Test Passed (PHPUnit\TestFixture\Issue4975Test::testSomething##2 second)
Test Finished (PHPUnit\TestFixture\Issue4975Test::testSomething##2 second)
Test Suite Finished (PHPUnit\TestFixture\Issue4975Test::testSomething, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Issue4975Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
