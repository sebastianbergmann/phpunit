--TEST--
phpunit --report-useless-tests AssertionOnMockTest ../_files/AssertionOnMockTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--report-useless-tests';
$_SERVER['argv'][3] = 'AssertionOnMockTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/AssertionOnMockTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R.R                                                                 3 / 3 (100%)

Time: %s, Memory: %sMb

OK, but incomplete, skipped, or risky tests!
Tests: 3, Assertions: 1, Risky: 2.
