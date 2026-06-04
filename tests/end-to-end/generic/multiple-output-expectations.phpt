--TEST--
A PHPUnit warning is triggered when an output expectation is configured more than once
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/MultipleOutputExpectationsTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

WW                                                                  2 / 2 (100%)

Time: %s, Memory: %s

2 tests triggered 2 PHPUnit warnings:

1) PHPUnit\TestFixture\MultipleOutputExpectationsTest::testExpectOutputStringThenExpectOutputRegex
Only one expectation on output can be configured: expectOutputString() and expectOutputRegex() cannot be combined and must not be called more than once

%s%eMultipleOutputExpectationsTest.php:16

2) PHPUnit\TestFixture\MultipleOutputExpectationsTest::testExpectOutputRegexThenExpectOutputString
Only one expectation on output can be configured: expectOutputString() and expectOutputRegex() cannot be combined and must not be called more than once

%s%eMultipleOutputExpectationsTest.php:24

OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Warnings: 2.
