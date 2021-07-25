--TEST--
GH-74: catchable fatal error in 3.5
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = __DIR__ . '/74/Issue74Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Issue74Test::testCreateAndThrowNewExceptionInProcessIsolation
NewException: Testing GH-74

%sIssue74Test.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
