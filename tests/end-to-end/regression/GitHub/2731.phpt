--TEST--
GH-2731: Empty exception message cannot be expected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/2731/Issue2731Test.php';

require_once __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue2731Test::testOne
Failed asserting that exception message is empty but is 'message'.

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
