--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1437
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5342/Issue5342Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue5342Test::testFailure
Failed asserting that false is true.

%sIssue5342Test.php:%i

--

There was 1 risky test:

1) PHPUnit\TestFixture\Issue5342Test::testFailure
Test code or tested code closed output buffers other than its own

%sIssue5342Test.php:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1, Risky: 1.
