--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3396
--FILE--
<?php declare(strict_types=1);
$tmpResultCache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__);

\copy(__DIR__ . '/../../../_files/DataproviderExecutionOrderTest_result_cache.txt', $tmpResultCache);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--order-by=defects';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][] = \dirname(\dirname(\dirname(__DIR__))) . '/_files/DataproviderExecutionOrderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testFirstTestThatAlwaysWorks' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testFirstTestThatAlwaysWorks' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testTestInTheMiddleThatAlwaysWorks' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testTestInTheMiddleThatAlwaysWorks' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+2=3" (1, 2, 3)' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "2+1=3" (2, 1, 3)' ended
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' started
Test 'PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)' ended


Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)
Failed asserting that 2 is identical to 3.

%s%etests%e_files%eDataproviderExecutionOrderTest.php:%d

2) PHPUnit\TestFixture\DataproviderExecutionOrderTest::testAddMoreNumbersWithADataprovider with data set "1+1=3" (1, 1, 3)
Failed asserting that 2 is identical to 3.

%s%etests%e_files%eDataproviderExecutionOrderTest.php:%d

FAILURES!
Tests: 8, Assertions: 8, Failures: 2.
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__));
