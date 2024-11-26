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
 ⚠ Expect user deprecation message n o t ignoring deprecations
 ✔ Expect user deprecation message a n d ignoring deprecations
 ✔ Pcre has utf 8 support
 ✔ Stream to non writable file with p h p unit error handler
 ✔ Stream to non writable file without p h p unit error handler
 ✔ Stream to invalid file

1 test triggered 1 deprecation:

1) %sFooTest.php:33
foo

OK, but there were issues!
Tests: 6, Assertions: 7, Deprecations: 1.
