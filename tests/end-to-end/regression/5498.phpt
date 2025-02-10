--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5498
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/5498/TestCase.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5498';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Bootstrap Finished (%sTestCase.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (CLI Arguments, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5498\Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5498\Test::testOne)
Before Test Method Called (PHPUnit\TestFixture\Issue5498\Test::parentBefore)
Before Test Method Called (PHPUnit\TestFixture\Issue5498\Test::before)
Before Test Method Finished:
- PHPUnit\TestFixture\Issue5498\Test::parentBefore
- PHPUnit\TestFixture\Issue5498\Test::before
Test Prepared (PHPUnit\TestFixture\Issue5498\Test::testOne)
After Test Method Called (PHPUnit\TestFixture\Issue5498\Test::after)
After Test Method Called (PHPUnit\TestFixture\Issue5498\Test::parentAfter)
After Test Method Finished:
- PHPUnit\TestFixture\Issue5498\Test::after
- PHPUnit\TestFixture\Issue5498\Test::parentAfter
Test Passed (PHPUnit\TestFixture\Issue5498\Test::testOne)
Test Finished (PHPUnit\TestFixture\Issue5498\Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Issue5498\Test, 1 test)
Test Suite Finished (CLI Arguments, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
