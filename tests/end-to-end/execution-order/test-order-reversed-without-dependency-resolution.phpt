--TEST--
phpunit --order-by=no-depends,reverse ../_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = '--order-by=no-depends,reverse';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../execution-order/_files/MultiDependencyTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
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
