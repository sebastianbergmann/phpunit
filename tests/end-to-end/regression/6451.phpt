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

1) Version requirement ">= 8" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpVersion() is incomplete, expected a version that consists of major, minor, and patch level ("8.5.0" instead of "8.5", for example)

2) Version requirement ">= 10" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpunitVersion() is incomplete, expected a version that consists of major, minor, and patch level ("8.5.0" instead of "8.5", for example)

3) Version requirement ">= 1" used by PHPUnit\TestFixture\Issue6451\Issue6451Test::testIncompletePhpExtensionVersion() is incomplete, expected a version that consists of major, minor, and patch level ("8.5.0" instead of "8.5", for example)

OK, but there were issues!
Tests: 4, Assertions: 4, PHPUnit Warnings: 6.
