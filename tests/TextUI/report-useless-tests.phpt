--TEST--
phpunit NothingTest ../_files/NothingTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'NothingTest';
$_SERVER['argv'][3] = __DIR__ . '/../_files/NothingTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Risky: 1.
