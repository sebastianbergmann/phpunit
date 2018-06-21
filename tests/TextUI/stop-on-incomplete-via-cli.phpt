--TEST--
phpunit --stop-on-error StopOnErrorTestSuite ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--stop-on-incomplete';
$_SERVER['argv'][3] = 'StopOnErrorTestSuite';
$_SERVER['argv'][4] = __DIR__ . '/../_files/StopOnErrorTestSuite.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

I

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
