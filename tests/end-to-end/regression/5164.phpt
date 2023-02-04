--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5164
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/5164/Issue5164Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--XFAIL--
https://github.com/sebastianbergmann/phpunit/issues/5164
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S

Time: %s, Memory: %s

There was 1 skipped test:

1) Issue5164Test
message

%sIssue5164Test.php:%d

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
