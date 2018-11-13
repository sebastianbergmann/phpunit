--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3396
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--order-by=defects';
$_SERVER['argv'][3] = '--testdox';
$_SERVER['argv'][4] = \dirname(\dirname(\dirname(__DIR__))) . '/../_files/DataproviderExecutionOrderTest.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

DataproviderExecutionOrder
 ✔ First test that always works
 ✔ Add numbers with a dataprovider with data set "1+2=3"
 ✔ Add numbers with a dataprovider with data set "2+1=3"
 ✘ Add numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:24
   │%w

 ✔ Test in the middle that always works
 ✔ Add more numbers with a dataprovider with data set "1+2=3"
 ✔ Add more numbers with a dataprovider with data set "2+1=3"
 ✘ Add more numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:37
   │%w
%A
Time: %s, Memory: %s

Summary of non-successful tests:
%A
FAILURES!
Tests: 8, Assertions: 8, Failures: 2.
