--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6197
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectErrorLogTest.php';

ini_set('open_basedir', (ini_get('open_basedir') ? ini_get('open_basedir') . PATH_SEPARATOR : '') . dirname(__DIR__, 3));

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
logged a side effect
Test Errored (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Could not create writable file for error_log()
Test Finished (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExpectErrorLog\ExpectErrorLogTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
