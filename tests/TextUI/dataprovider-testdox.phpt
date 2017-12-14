--TEST--
phpunit DataProviderTestDoxTest ../_files/DataProviderTestDoxTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = 'DataProviderTestDoxTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderTestDoxTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

DataProviderTestDox
 ✔ Does something with data set "one" [%f ms]
 ✔ Does something with data set "two" [%f ms]
 ✔ Does something else with data set "one" [%f ms]
 ✔ Does something else with data set "two" [%f ms]


Time: %d ms, Memory: %fMB

OK (4 tests, 4 assertions)
