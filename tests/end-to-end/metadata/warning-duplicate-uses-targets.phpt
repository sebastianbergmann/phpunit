--TEST--
phpunit ../_files/code-coverage-targeting/DuplicateUsesTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/tests/DuplicateUsesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\CodeCoverageTargeting\Warnings\DuplicateUsesTest::testOne
Class PHPUnit\TestFixture\CodeCoverageTargeting\Warnings\SomeClass is targeted multiple times by the same "Uses" attribute

%sDuplicateUsesTest.php:%d

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
