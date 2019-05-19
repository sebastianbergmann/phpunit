--TEST--
phpunit --order-by=depends,random --random-order-seed=54321 ../_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--debug',
    '--order-by=depends,random',
    '--random-order-seed=54321',
    'MultiDependencyTest',
    \realpath(__DIR__ . '/../execution-order/_files/MultiDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

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
