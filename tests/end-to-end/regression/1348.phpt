--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1348
--SKIPIF--
<?php declare(strict_types=1);
if (defined('STDOUT')) {
    print 'skip: PHP < 8.3 required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][]  = '--process-isolation';
$_SERVER['argv'][]  = __DIR__ . '/1348/Issue1348Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue1348Test::testSTDERR
PHPUnit\Framework\Exception: STDERR works as usual.

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
