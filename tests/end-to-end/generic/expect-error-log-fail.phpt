--TEST--
https://github.com/sebastianbergmann/phpunit/issues/2155
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectErrorLogFailTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Failed (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test did not call error_log().
Failed asserting that a string is not empty.
Test Finished (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
