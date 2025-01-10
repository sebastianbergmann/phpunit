--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6094
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6094/Issue6094Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue6094\Issue6094Test
Exception: message

%sIssue6094Test.php:19

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
