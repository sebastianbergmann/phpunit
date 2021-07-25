--TEST--
phpunit --order-by=defects ./tests/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$tmpResultCache = \tempnam(sys_get_temp_dir(), __FILE__);
\copy(__DIR__ . '/_files/MultiDependencyTest_result_cache.txt', $tmpResultCache);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--order-by=defects';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/MultiDependencyTest.php');

require_once __DIR__ . '/../../bootstrap.php';

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
