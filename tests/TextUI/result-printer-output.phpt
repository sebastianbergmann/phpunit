--TEST--
phpunit --no-configuration ../_files/ResultPrinterDebugOutputTestCase.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/../_files/ResultPrinterDebugOutputTestCase.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.foo.bar                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 0 assertions)
