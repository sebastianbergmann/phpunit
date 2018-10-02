--TEST--
phpunit DataProviderTestDoxTest ../../_files/DataProviderTestDoxTest.php
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
 ✔ Does something with data set "one"
 ✔ Does something with data set "two"
 ✔ Does something else with data set "one"
 ✔ Does something else with data set "two"
 ✔ With provider with indexed array with data set #0
 ✔ With provider with indexed array with data set #1
 ✔ ... true ...
 ✔ ... 1 ...
 ✔ ... 1.0 ...
 ✔ ... string ...
 ✔ ... array ...
 ✔ ... object ...
 ✔ ... string ...
 ✔ ... resource ...
 ✔ ... NULL ...

Time: %s, Memory: %s

OK (15 tests, 15 assertions)
