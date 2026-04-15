--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6451
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6451/Issue6451Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit test runner warnings:

1) Incomplete version requirement "8" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpVersion()

2) Incomplete version requirement "10" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpunitVersion()

3) Incomplete version requirement "1" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpExtensionVersion()

OK, but there were issues!
Tests: 4, Assertions: 4, PHPUnit Warnings: 6.
