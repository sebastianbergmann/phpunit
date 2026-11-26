--TEST--
https://github.com/sebastianbergmann/phpunit/issues/2155
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectErrorLogTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Test Passed (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Test Finished (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
