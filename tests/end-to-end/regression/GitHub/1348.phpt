--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1348
--SKIPIF--
<?php declare(strict_types=1);
if (defined('HHVM_VERSION') || defined('PHPDBG_VERSION')) {
    print 'skip: PHP runtime required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][]  = '--process-isolation';
$_SERVER['argv'][]  = __DIR__ . '/1348/Issue1348Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.
STDOUT does not break test result
E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Issue1348Test::testSTDERR
PHPUnit\Framework\Exception: STDERR works as usual.

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
