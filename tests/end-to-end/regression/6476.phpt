--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6476
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6476/Issue6476Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue6408\Issue6476Test::testIteratorAggregate
PHPUnit\Framework\Exception: IteratorAggregate::getIterator() returned an object that was already seen

%sIssue6476Test.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
