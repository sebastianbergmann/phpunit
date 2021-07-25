--TEST--
GH-765: Fatal error triggered in PHPUnit when exception is thrown in data provider of a test with a dependency
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/765/Issue765Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Error
The data provider specified for Issue765Test::testDependent is invalid.
Exception: <no message>
%sIssue765Test.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
