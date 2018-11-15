--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3396
--FILE--
<?php
$tmpResultCache = tempnam(sys_get_temp_dir(), __FILE__);
file_put_contents($tmpResultCache, file_get_contents(__DIR__ . '/../../../../_files/DataproviderExecutionOrderTest_result_cache.txt'));

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--order-by=defects';
$_SERVER['argv'][3] = '--debug';
$_SERVER['argv'][4] = '--cache-result';
$_SERVER['argv'][5] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][6] = \dirname(\dirname(\dirname(__DIR__))) . '/../_files/DataproviderExecutionOrderTest.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();

unlink($tmpResultCache);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' started
Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' ended
Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' started
Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' ended
Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' started
Test 'DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' ended
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' started
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' ended
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' started
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' ended
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' started
Test 'DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' ended
Test 'DataproviderExecutionOrderTest::testFirstTestThatAlwaysWorks' started
Test 'DataproviderExecutionOrderTest::testFirstTestThatAlwaysWorks' ended
Test 'DataproviderExecutionOrderTest::testTestInTheMiddleThatAlwaysWorks' started
Test 'DataproviderExecutionOrderTest::testTestInTheMiddleThatAlwaysWorks' ended


Time: %s, Memory: %s

There were 2 failures:

1) DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)
Failed asserting that 2 is identical to 3.

%s%etests%e_files%eDataproviderExecutionOrderTest.php:%d

2) DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)
Failed asserting that 2 is identical to 3.

%s%etests%e_files%eDataproviderExecutionOrderTest.php:%d

FAILURES!
Tests: 8, Assertions: 8, Failures: 2.
