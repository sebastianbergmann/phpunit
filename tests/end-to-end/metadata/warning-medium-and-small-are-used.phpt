--TEST--
phpunit ../_files/size-combinations/MediumSmallTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/size-combinations/MediumSmallTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) #[Small] cannot be combined with #[Medium] or #[Large] for class PHPUnit\TestFixture\SizeCombinations\MediumSmallTest

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
