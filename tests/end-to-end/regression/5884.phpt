--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5884
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/5884';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

Time: %s, Memory: %s

Foo (PHPUnit\TestFixture\Issue5884\Foo)
 ✔ Pcre has utf 8 support
 ✔ Stream to non writable file with p h p unit error handler
 ✔ Stream to non writable file without p h p unit error handler
 ✔ Stream to invalid file

OK, but some tests were skipped!
Tests: 6, Assertions: 5, Skipped: 2.
