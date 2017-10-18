--TEST--
phpunit --debug DataProviderDebugTest ../_files/DataProviderDebugTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = 'DataProviderDebugTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderDebugTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'DataProviderDebugTest::testProvider with data set #0 (null, true, 1, 1.0)' started
Test 'DataProviderDebugTest::testProvider with data set #0 (null, true, 1, 1.0)' ended
Test 'DataProviderDebugTest::testProvider with data set #1 (1.2, resource(%s) of type (stream), '1')' started
Test 'DataProviderDebugTest::testProvider with data set #1 (1.2, resource(%s) of type (stream), '1')' ended
Test 'DataProviderDebugTest::testProvider with data set #2 (array(array(1, 2, 3), array(3, 4, 5)))' started
Test 'DataProviderDebugTest::testProvider with data set #2 (array(array(1, 2, 3), array(3, 4, 5)))' ended
Test 'DataProviderDebugTest::testProvider with data set #3 ('this\nis\na\nvery\nvery\nvery...\rtext')' started
Test 'DataProviderDebugTest::testProvider with data set #3 ('this\nis\na\nvery\nvery\nvery...\rtext')' ended
Test 'DataProviderDebugTest::testProvider with data set #4 (stdClass Object (), stdClass Object (...), array(), SplObjectStorage Object (...), stdClass Object (...))' started
Test 'DataProviderDebugTest::testProvider with data set #4 (stdClass Object (), stdClass Object (...), array(), SplObjectStorage Object (...), stdClass Object (...))' ended
Test 'DataProviderDebugTest::testProvider with data set #5 (Binary String: 0x000102030405, Binary String: 0x0e0f101112131...c1d1e1f)' started
Test 'DataProviderDebugTest::testProvider with data set #5 (Binary String: 0x000102030405, Binary String: 0x0e0f101112131...c1d1e1f)' ended
Test 'DataProviderDebugTest::testProvider with data set #6 (Binary String: 0x0009)' started
Test 'DataProviderDebugTest::testProvider with data set #6 (Binary String: 0x0009)' ended


Time: %s, Memory: %s

OK (7 tests, 7 assertions)
