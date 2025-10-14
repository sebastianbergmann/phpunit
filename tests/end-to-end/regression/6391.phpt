--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6391
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--static-backup';
$_SERVER['argv'][] = __DIR__ . '/6391/tests/Issue6391Test.php';

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
Test Suite Started (TestFixture\Issue6391\Issue6391Test, 1 test)
Before First Test Method Called (TestFixture\Issue6391\Issue6391Test::setUpBeforeClass)
Before First Test Method Finished:
- TestFixture\Issue6391\Issue6391Test::setUpBeforeClass
Test Preparation Started (TestFixture\Issue6391\Issue6391Test::testOne)
Test Preparation Failed (TestFixture\Issue6391\Issue6391Test::testOne)
Test Errored (TestFixture\Issue6391\Issue6391Test::testOne)
Object of class TestFixture\Issue6391\Issue6391 could not be converted to string
Test Suite Finished (TestFixture\Issue6391\Issue6391Test, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
