--TEST--
phpunit --order-by=no-depends,reverse --cache-result --cache-result-file ./tests/_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$target = tempnam(sys_get_temp_dir(), __FILE__);

$arguments = [
    '--no-configuration',
    '--ignore-dependencies',   // keep coverage for legacy CLI option
    '--order-by=reverse',
    '--cache-result',
    '--cache-result-file=' . $target,
    realpath(__DIR__ . '/../execution-order/_files/MultiDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main(false);

print file_get_contents($target);

unlink($target);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.SS..                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 5, Assertions: 3, Skipped: 2.
C:37:"PHPUnit\Runner\DefaultTestResultCache":%d:{a:2:{s:7:"defects";a:2:{s:29:"MultiDependencyTest::testFour";i:1;s:30:"MultiDependencyTest::testThree";i:1;}s:5:"times";a:5:{s:29:"MultiDependencyTest::testFive";d:%f;s:29:"MultiDependencyTest::testFour";d:%f;s:30:"MultiDependencyTest::testThree";d:%f;s:28:"MultiDependencyTest::testTwo";d:%f;s:28:"MultiDependencyTest::testOne";d:%f;}}}
