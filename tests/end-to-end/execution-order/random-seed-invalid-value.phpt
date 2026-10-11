--TEST--
--random-seed with invalid value triggers warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'random';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-duration';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   %d

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Option "--random-seed 0" ignored because "0" is not a positive integer

OK, but there were issues!
Tests: 6, Assertions: 6, PHPUnit Warnings: 1.
