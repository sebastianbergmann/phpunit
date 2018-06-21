--TEST--
phpunit -c ../_files/configuration_stop_on_incomplete.xml StopOnErrorTestSuite ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php
$_SERVER['argv'][1] = '-c';
$_SERVER['argv'][2] = __DIR__ . '/../_files/configuration_stop_on_incomplete.xml';
$_SERVER['argv'][4] = 'StopOnErrorTestSuite';
$_SERVER['argv'][5] = __DIR__ . '/../_files/StopOnErrorTestSuite.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

I

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
