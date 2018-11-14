--TEST--
phpunit --reverse-order --cache-result --cache-result-file MultiDependencyTest ./tests/_files/MultiDependencyTest.php
--FILE--
<?php
$target = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--reverse-order';
$_SERVER['argv'][3] = '--cache-result';
$_SERVER['argv'][4] = '--cache-result-file=' . $target;
$_SERVER['argv'][5] = 'MultiDependencyTest';
$_SERVER['argv'][6] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main(false);

print file_get_contents($target);

unlink($target);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.SS..                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 5, Assertions: 3, Skipped: 2.
C:30:"PHPUnit\Runner\TestResultCache":%d:{a:2:{s:7:"defects";a:2:{s:29:"MultiDependencyTest::testFour";i:1;s:30:"MultiDependencyTest::testThree";i:1;}s:5:"times";a:5:{s:29:"MultiDependencyTest::testFive";d:%f;s:29:"MultiDependencyTest::testFour";d:%f;s:30:"MultiDependencyTest::testThree";d:%f;s:28:"MultiDependencyTest::testTwo";d:%f;s:28:"MultiDependencyTest::testOne";d:%f;}}}
