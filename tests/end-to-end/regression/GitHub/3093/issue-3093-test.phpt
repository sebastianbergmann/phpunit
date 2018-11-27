--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3093
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--order-by=reverse';
$_SERVER['argv'][3] = __DIR__ . '/Issue3093Test.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
