--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5616
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5616/Issue5616Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) PHPUnit\TestFixture\Issue5616\Issue5616Test::testOne with data set #0 (1, '2', 3.0, true)
Failed asserting that false is true.

%sIssue5616Test.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
