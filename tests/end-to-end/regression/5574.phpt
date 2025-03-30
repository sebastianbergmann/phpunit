--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5574
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

Time: %s, Memory: %s MB

There was 1 error:

1) PHPUnit\TestFixture\Issue5574\Issue5574Test::testThrownWrappedThrowablesOutputsCorrectStackTraceForEach
Exception: My exception

%sIssue5574Test.php:22

Caused by
Error: Inner Exception

%sIssue5574Test.php:21

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
