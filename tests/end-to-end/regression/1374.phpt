--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1374
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/1374/Issue1374Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
