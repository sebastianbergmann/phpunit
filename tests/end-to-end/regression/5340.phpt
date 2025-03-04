--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5340
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = __DIR__ . '/5340/Issue5340Test.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

%s

output printed from passing test
R
output printed from failing test
F                                                                  2 / 2 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) Issue5340Test::testTwo
Failed asserting that false is true.

%s

--

There were 2 risky tests:

1) Issue5340Test::testOne
Test code or tested code printed unexpected output: output printed from passing test

%s%eIssue5340Test.php:%d

2) Issue5340Test::testTwo
Test code or tested code printed unexpected output: 
output printed from failing test

%s%eIssue5340Test.php:%d

FAILURES!
Tests: 2, Assertions: 2, Failures: 1, Risky: 2.
