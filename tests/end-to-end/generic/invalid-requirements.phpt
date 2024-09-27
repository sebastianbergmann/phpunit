--TEST--
phpunit --no-configuration ../_files/InvalidRequirementsTest.php
--FILE--
<?php declare(strict_types=1);
require_once(__DIR__ . '/../../bootstrap.php');

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/InvalidRequirementsTest.php');

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit test runner warnings:

1) Class PHPUnit\TestFixture\InvalidRequirementsTest is annotated using an invalid version requirement: Version constraint ~~9.0 is not supported.

2) Method PHPUnit\TestFixture\InvalidRequirementsTest::testInvalidVersionConstraint is annotated using an invalid version requirement: Version constraint ~~9.0 is not supported.

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 2.
