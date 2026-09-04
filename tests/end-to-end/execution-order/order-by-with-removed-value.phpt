--TEST--
Using a value that PHPUnit no longer supports for --order-by is an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'depends';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-sizes';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

"depends" is no longer supported for --order-by, use the --resolve-dependencies CLI option instead
