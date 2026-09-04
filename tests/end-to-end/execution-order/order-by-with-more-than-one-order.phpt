--TEST--
Using more than one order for --order-by is an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'duration-ascending,duration-descending';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-sizes';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot use more than one order for --order-by: "duration-ascending" and "duration-descending"
