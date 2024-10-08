--TEST--
phpunit --order-by=no-depends,reverse --cache-result --cache-result-file ./tests/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--ignore-dependencies';   // keep coverage for legacy CLI option
$_SERVER['argv'][] = '--order-by=reverse';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = '--cache-directory=' . $cacheDirectory;
$_SERVER['argv'][] = __DIR__ . '/../../_files/MultiDependencyTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($cacheDirectory . DIRECTORY_SEPARATOR . 'test-results');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.SS..                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 5, Assertions: 3, Skipped: 2.
{"version":1,"defects":{"PHPUnit\\TestFixture\\MultiDependencyTest::testFour":1,"PHPUnit\\TestFixture\\MultiDependencyTest::testThree":1},"times":{"PHPUnit\\TestFixture\\MultiDependencyTest::testFive":%f,"PHPUnit\\TestFixture\\MultiDependencyTest::testFour":%f,"PHPUnit\\TestFixture\\MultiDependencyTest::testThree":%f,"PHPUnit\\TestFixture\\MultiDependencyTest::testTwo":%f,"PHPUnit\\TestFixture\\MultiDependencyTest::testOne":%f}}
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__) . DIRECTORY_SEPARATOR . 'test-results');
rmdir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__));
