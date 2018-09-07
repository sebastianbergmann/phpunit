--TEST--
phpunit --reverse-order --ignore-dependencies ../_files/MultiDependencyTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = '--reverse-order';
$_SERVER['argv'][5] = '--ignore-dependencies';
$_SERVER['argv'][6] = 'MultiDependencyTest';
$_SERVER['argv'][7] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Test 'MultiDependencyTest::testFive' started
Test 'MultiDependencyTest::testFive' ended
Test 'MultiDependencyTest::testFour' started
Test 'MultiDependencyTest::testFour' ended
Test 'MultiDependencyTest::testThree' started
Test 'MultiDependencyTest::testThree' ended
Test 'MultiDependencyTest::testTwo' started
Test 'MultiDependencyTest::testTwo' ended
Test 'MultiDependencyTest::testOne' started
Test 'MultiDependencyTest::testOne' ended


Time: %s, Memory: %s

There were 2 skipped tests:

1) MultiDependencyTest::testFour
This test depends on "MultiDependencyTest::testThree" to pass.

2) MultiDependencyTest::testThree
This test depends on "MultiDependencyTest::testOne" to pass.

OK, but incomplete, skipped, or risky tests!
Tests: 5, Assertions: 3, Skipped: 2.
