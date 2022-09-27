--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3380
--FILE--
<?php declare(strict_types=1);
$tmpResultCache = tempnam(sys_get_temp_dir(), __FILE__);
\copy(__DIR__ . '/../../../_files/DataproviderExecutionOrderTest_result_cache.txt', $tmpResultCache);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--order-by=defects';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][] = \dirname(\dirname(\dirname(__DIR__))) . '/_files/DataproviderExecutionOrderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();

unlink($tmpResultCache);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Dataprovider Execution Order
 ✔ First test that always works
 ✔ Add numbers with a dataprovider with data set "1+2=3"
 ✔ Add numbers with a dataprovider with data set "2+1=3"
 ✘ Add numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:%d
   │%w

 ✔ Test in the middle that always works
 ✔ Add more numbers with a dataprovider with data set "1+2=3"
 ✔ Add more numbers with a dataprovider with data set "2+1=3"
 ✘ Add more numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:%d
   │%w

Time: %s, Memory: %s

Summary of non-successful tests:

Dataprovider Execution Order
 ✘ Add numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:%d
   │%w

 ✘ Add more numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │%w
   │ %s%etests%e_files%eDataproviderExecutionOrderTest.php:%d
   │%w

FAILURES!
Tests: 8, Assertions: 8, Failures: 2.
