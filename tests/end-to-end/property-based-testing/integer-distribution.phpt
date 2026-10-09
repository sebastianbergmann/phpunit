--TEST--
Every value generated for int, int<-9223372036854775807, max>, and Generators::integers() is an int within the range, and negative, non-negative, odd, and even values occur
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/IntegerDistributionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

OK (6 tests, %d assertions)
