--TEST--
Values of int, of integer ranges, and of positive-int, negative-int, non-negative-int, non-positive-int, and non-zero-int are generated for parameters of these types
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/IntegerTypesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.........                                                           9 / 9 (100%)

Time: %s, Memory: %s

OK (9 tests, %d assertions)
