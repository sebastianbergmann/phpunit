--TEST--
phpunit -c ../_files/configuration_stop_on_defect.xml MultiDependencyTest ./tests/_files/MultiDependencyTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--debug';
$_SERVER['argv'][2] = '-c';
$_SERVER['argv'][3] = __DIR__ . '/../_files/configuration_execution_order_options.xml';
$_SERVER['argv'][4] = 'MultiDependencyTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'MultiDependencyTest::testFive' started
Test 'MultiDependencyTest::testFive' ended
Test 'MultiDependencyTest::testTwo' started
Test 'MultiDependencyTest::testTwo' ended
Test 'MultiDependencyTest::testOne' started
Test 'MultiDependencyTest::testOne' ended
Test 'MultiDependencyTest::testThree' started
Test 'MultiDependencyTest::testThree' ended
Test 'MultiDependencyTest::testFour' started
Test 'MultiDependencyTest::testFour' ended


Time: %s, Memory: %s

OK (5 tests, 6 assertions)
