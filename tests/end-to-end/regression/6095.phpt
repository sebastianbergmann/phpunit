--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6095
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6095/Issue6095Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue6095\Issue6095Test::testOne
PHPUnit\TestFixture\MockObject\AnInterface::doSomething(): bool was not expected to be called more than once.

%sIssue6095Test.php:26

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
