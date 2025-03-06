--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5976
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/5976/Issue5976Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

These before-first-test methods errored:

1) PHPUnit\TestFixture\Issue5967\Issue5976Test::setUpBeforeClass
Exception: message

%sIssue5976Test.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
