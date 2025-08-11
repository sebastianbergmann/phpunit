--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6281
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/6281/Issue6281Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Issue6281\Issue6281Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue6281\Issue6281Test::testOne)
Before Test Method Called (PHPUnit\TestFixture\Issue6281\Issue6281Test::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Issue6281\Issue6281Test::setUp
Test Skipped (PHPUnit\TestFixture\Issue6281\Issue6281Test::testOne)
skip message
After Test Method Called (PHPUnit\TestFixture\Issue6281\Issue6281Test::tearDown)
After Test Method Errored (PHPUnit\TestFixture\Issue6281\Issue6281Test::tearDown)
exception message
After Test Method Finished:
- PHPUnit\TestFixture\Issue6281\Issue6281Test::tearDown
Test Errored (PHPUnit\TestFixture\Issue6281\Issue6281Test::testOne)
exception message
Test Suite Finished (PHPUnit\TestFixture\Issue6281\Issue6281Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
