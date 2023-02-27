--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5258
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = '/dev/null';
$_SERVER['argv'][] = __DIR__ . '/5258/Issue5258Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--XFAIL--
https://github.com/sebastianbergmann/phpunit/issues/5258
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.S                                                                  2 / 2 (100%)

Time: %s, Memory: %s MB

OK, but some tests were skipped!
Tests: 2, Assertions: 1, Skipped: 1.
