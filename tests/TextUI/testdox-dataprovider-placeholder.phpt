--TEST--
phpunit --testdox RouterTest ../_files/RouterTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = 'RouterTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/RouterTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Router
 ✔ Routes /foo/bar to FooBarHandler

Time: %s, Memory: %s

OK (1 test, 1 assertion)
