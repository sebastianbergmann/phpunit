--TEST--
GH-863: Number of tests to run calculated incorrectly when --repeat is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '50';
$_SERVER['argv'][] = \dirname(\dirname(\dirname(__DIR__))) . '/_files/BankAccountTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...............................................................  63 / 150 ( 42%)
............................................................... 126 / 150 ( 84%)
........................                                        150 / 150 (100%)

Time: %s, Memory: %s

OK (150 tests, 150 assertions)
