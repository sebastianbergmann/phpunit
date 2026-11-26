--TEST--
The seed of the test run is printed when the test suite contains a property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/PassingPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   %d

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 10 assertions)
