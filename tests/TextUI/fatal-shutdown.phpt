--TEST--
phpunit FatalTest --process-isolation ../_files/FatalTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][3] = 'FatalShutdownTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/FatalShutdownTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E
Fatal error: Cannot redeclare class FatalShutdownTest in %s(16) : eval()'d code on line 1


Time: %s, Memory: %sMb

There was 1 error:

1) FatalShutdownTest::testWarning
FatalShutdownTest warning

%stests/_files/FatalShutdownTest.php:7

FAILURES!
Tests: 2, Assertions: 0, Errors: 1.
