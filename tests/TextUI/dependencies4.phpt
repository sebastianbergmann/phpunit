--TEST--
phpunit DependencyExtendTest ../_files/DependencyExtendTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'DependencyExtendTest';
$_SERVER['argv'][3] = __DIR__ . '/../_files/DependencyExtendTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
