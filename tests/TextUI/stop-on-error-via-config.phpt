--TEST--
phpunit -c ../_files/configuration_stop_on_error.xml StopOnErrorTestSuite ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php
$_SERVER['argv'][1] = '-c';
$_SERVER['argv'][2] = __DIR__ . '/../_files/configuration_stop_on_error.xml';
$_SERVER['argv'][4] = 'StopOnErrorTestSuite';
$_SERVER['argv'][5] = __DIR__ . '/../_files/StopOnErrorTestSuite.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

IE

Time: %s, Memory: %s

There was 1 error:

1) StopOnErrorTestSuite::testWithError
Error: StopOnErrorTestSuite_error

%s%etests%e_files%eStopOnErrorTestSuite.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1, Incomplete: 1.
