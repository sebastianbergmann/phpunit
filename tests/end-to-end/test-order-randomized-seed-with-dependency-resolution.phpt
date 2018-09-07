--TEST--
phpunit --random-order --random-order-seed=54321 --resolve-dependencies ../_files/MultiDependencyTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = '--random-order';
$_SERVER['argv'][5] = '--random-order-seed=54321';
$_SERVER['argv'][6] = '--resolve-dependencies';
$_SERVER['argv'][7] = 'MultiDependencyTest';
$_SERVER['argv'][8] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random seed:   54321

Test 'MultiDependencyTest::testTwo' started
Test 'MultiDependencyTest::testTwo' ended
Test 'MultiDependencyTest::testFive' started
Test 'MultiDependencyTest::testFive' ended
Test 'MultiDependencyTest::testOne' started
Test 'MultiDependencyTest::testOne' ended
Test 'MultiDependencyTest::testThree' started
Test 'MultiDependencyTest::testThree' ended
Test 'MultiDependencyTest::testFour' started
Test 'MultiDependencyTest::testFour' ended


Time: %s, Memory: %s

OK (5 tests, 6 assertions)
