--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6368
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/6368/phpunit.xml';

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
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue6368\Issue6368Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue6368\Issue6368Test::testOne)
Test Prepared (PHPUnit\TestFixture\Issue6368\Issue6368Test::testOne)
Test Runner Triggered Warning (message)
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\Issue6368\Issue6368Test::testOne)
message
Test Passed (PHPUnit\TestFixture\Issue6368\Issue6368Test::testOne)
Test Finished (PHPUnit\TestFixture\Issue6368\Issue6368Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Issue6368\Issue6368Test, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
