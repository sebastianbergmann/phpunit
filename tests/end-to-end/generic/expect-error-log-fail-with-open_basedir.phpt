--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6197
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
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
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Expect Error Log Fail (PHPUnit\TestFixture\ExpectNoErrorLog\ExpectErrorLogFail)
 ∅ One
   │
   │ Could not create writable error_log file.

   │

OK, but there were issues!
Tests: 1, Assertions: 1, Incomplete: 1.
