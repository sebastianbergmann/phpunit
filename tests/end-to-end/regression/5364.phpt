--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5364
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5364';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Class PHPUnit\TestFixture\Issue5364\BarTest declared in %sBarTest.php does not extend PHPUnit\Framework\TestCase

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
