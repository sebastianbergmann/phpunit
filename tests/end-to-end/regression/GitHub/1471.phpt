--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1471
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/1471/Issue1471Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Issue1471Test::testFailure
Failed asserting that false is true.

%s%eIssue1471Test.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
