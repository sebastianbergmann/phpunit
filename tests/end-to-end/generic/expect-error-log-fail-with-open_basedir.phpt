--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6197
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectErrorLogFailTest.php';

/*
 * Expected result should match the result of expect-error-log-fail-with-open_basedir.phpt test,
 * but at least one of these feature requests needs to be implemented in php-src:
 * - https://github.com/php/php-src/issues/17817
 * - https://github.com/php/php-src/issues/18530
 *
 * Until then, mark the test result as incomplete when TestCase::expectErrorLog() was called and an error_log file
 * could not be created (because of open_basedir php.ini in effect, readonly filesystem...).
 */

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
Test Suite Started (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Errored (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Could not create writable file for error_log()
Test Finished (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFailTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
