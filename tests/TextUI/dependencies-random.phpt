--TEST--
Test the --random-order command line flag and "Random seed" default printer
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--verbose';
$_SERVER['argv'][3] = '--random-order';
$_SERVER['argv'][4] = '--reorder-dependencies';
$_SERVER['argv'][5] = 'MultiDependencyTest';
$_SERVER['argv'][6] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random seed:   %d

....                                                                4 / 4 (100%)

Time: %s %s, Memory: %s

OK (4 tests, 5 assertions)
