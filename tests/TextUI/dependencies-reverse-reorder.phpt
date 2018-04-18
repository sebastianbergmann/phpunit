--TEST--
phpunit --verbose --reverse-order ../_files/DependencySuccessTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = '--reverse-order';
$_SERVER['argv'][5] = 'MultiDependencyTest';
$_SERVER['argv'][6] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Test 'MultiDependencyTest::testTwo' started
Test 'MultiDependencyTest::testTwo' ended
Test 'MultiDependencyTest::testOne' started
Test 'MultiDependencyTest::testOne' ended
Test 'MultiDependencyTest::testThree' started
Test 'MultiDependencyTest::testThree' ended
Test 'MultiDependencyTest::testFour' started
Test 'MultiDependencyTest::testFour' ended


Time: %s %s, Memory: %s

OK (4 tests, 5 assertions)
