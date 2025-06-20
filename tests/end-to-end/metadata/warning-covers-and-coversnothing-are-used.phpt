--TEST--
phpunit ../_files/code-coverage-targeting/CoversClassCoversNothingTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/../_files/code-coverage-targeting/tests/CoversClassCoversNothingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\CodeCoverageTargeting\Warnings\CoversClassCoversNothingTest::testOne
#[Covers*] and #[Uses*] attributes do not have an effect when the #[CoversNothing] attribute is used

%sCoversClassCoversNothingTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
