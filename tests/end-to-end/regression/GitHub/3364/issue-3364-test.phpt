--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3364
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = __DIR__ . '/Issue3364SetupBeforeClassTest.php';
$_SERVER['argv'][4] = __DIR__ . '/Issue3364SetupTest.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Time: %s, Memory: %s


ERRORS!
Tests: 4, Assertions: 0, Errors: 2.
