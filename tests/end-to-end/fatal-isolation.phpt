--TEST--
phpunit --process-isolation ../../_files/FatalTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = __DIR__ . '/../_files/FatalTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) FatalTest::testFatalError
%a
ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
