--TEST--
https://github.com/sebastianbergmann/phpunit/pull/3167 in class
--FILE--
<?php
$__stdout = fopen('php://stdout', 'w');
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][]  = 'Issue3167ClassTest';
$_SERVER['argv'][]  = __DIR__ . '/Issue3167ClassTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Issue3167ClassTest::testSTDOUT
Failed asserting that false is true.

%sIssue3167ClassTest.php:21

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
