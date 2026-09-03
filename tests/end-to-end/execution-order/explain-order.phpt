--TEST--
--explain-order prints the effective test reordering pipeline
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'size-ascending,defects';
$_SERVER['argv'][] = '--explain-order';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-sizes';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Order:         size-ascending, defects, depends

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
