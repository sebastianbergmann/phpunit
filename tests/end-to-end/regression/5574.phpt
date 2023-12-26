--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5567
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5574/Issue5574Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: 00:00.016, Memory: 4.00 MB

There was 1 error:

1) Issue5574Test::testOne
Exception: My exception

%sIssue5574Test.php:8

Caused by
Error: Inner Exception

%sIssue5574Test.php:8

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
