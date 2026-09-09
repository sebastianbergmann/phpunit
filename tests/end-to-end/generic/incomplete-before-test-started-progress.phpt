--TEST--
The progress of a test that is marked incomplete before it started is printed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/IncompleteInSetUpTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but there were issues!
Tests: 1, Assertions: 0, Incomplete: 1.
