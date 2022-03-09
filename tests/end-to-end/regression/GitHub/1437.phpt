--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1437
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/1437/Issue1437Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue1437Test::testFailure
Failed asserting that false is true.

%sIssue1437Test.php:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
