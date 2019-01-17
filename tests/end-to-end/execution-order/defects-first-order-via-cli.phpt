--TEST--
phpunit --order-by=defects MultiDependencyTest ./tests/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$tmpResultCache = \tempnam(sys_get_temp_dir(), __FILE__);
\file_put_contents($tmpResultCache, file_get_contents(__DIR__ . '/_files/MultiDependencyTest_result_cache.txt'));

$arguments = [
    '--no-configuration',
    '--debug',
    '--order-by=defects',
    '--cache-result',
    '--cache-result-file=' . $tmpResultCache,
    'MultiDependencyTest',
    \realpath(__DIR__ . '/_files/MultiDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();

\unlink($tmpResultCache);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'MultiDependencyTest::testFive' started
Test 'MultiDependencyTest::testFive' ended
Test 'MultiDependencyTest::testOne' started
Test 'MultiDependencyTest::testOne' ended
Test 'MultiDependencyTest::testTwo' started
Test 'MultiDependencyTest::testTwo' ended
Test 'MultiDependencyTest::testThree' started
Test 'MultiDependencyTest::testThree' ended
Test 'MultiDependencyTest::testFour' started
Test 'MultiDependencyTest::testFour' ended


Time: %s, Memory: %s

OK (5 tests, 6 assertions)
