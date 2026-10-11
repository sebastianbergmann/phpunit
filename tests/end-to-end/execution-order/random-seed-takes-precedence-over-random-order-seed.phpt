--TEST--
--random-seed takes precedence over --random-order-seed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'random';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--random-order-seed';
$_SERVER['argv'][] = '5678';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-duration';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   1234

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

OK, but there were issues!
Tests: 6, Assertions: 6, PHPUnit Deprecations: 1.
