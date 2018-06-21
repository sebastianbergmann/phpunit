--TEST--
phpunit --order-by=random --resolve-dependencies ../_files/MultiDependencyTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--verbose';
$_SERVER['argv'][3] = '--order-by=random';
$_SERVER['argv'][4] = '--resolve-dependencies';
$_SERVER['argv'][5] = 'MultiDependencyTest';
$_SERVER['argv'][6] = __DIR__ . '/../_files/MultiDependencyTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random seed:   %d

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK (5 tests, 6 assertions)
