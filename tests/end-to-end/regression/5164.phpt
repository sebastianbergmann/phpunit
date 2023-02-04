--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5164
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = __DIR__ . '/5164/Issue5164Test.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

SS                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 skipped tests:

1) Issue5164Test::testOne
message

%sIssue5164Test.php:%d

2) Issue5164Test::testTwo
message

%sIssue5164Test.php:%d

OK, but incomplete, skipped, or risky tests!
Tests: 2, Assertions: 0, Skipped: 2.
