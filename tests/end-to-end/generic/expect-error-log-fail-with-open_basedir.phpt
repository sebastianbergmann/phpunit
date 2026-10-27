--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6197
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
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

require_once __DIR__ . '/../../bootstrap.php';

/*
 * When this test is run with code coverage collection enabled, its code is
 * executed from a file in the directory PHPUnit creates for the temporary
 * files that are used to collect code coverage for a PHPT test. This file is
 * the one that is listed in $GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'],
 * and its directory is allowed so that this test also works in that case. The
 * system's temporary directory itself is not allowed: tmpfile(), which is
 * what TestCase::expectErrorLog() relies on, must still fail.
 */
$openBasedir = dirname(__DIR__, 3);

foreach ($GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'] ?? [] as $file) {
    $openBasedir .= PATH_SEPARATOR . dirname($file);
}

if (ini_get('open_basedir')) {
    $openBasedir = ini_get('open_basedir') . PATH_SEPARATOR . $openBasedir;
}

ini_set('open_basedir', $openBasedir);

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
