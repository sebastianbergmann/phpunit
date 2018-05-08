--TEST--
GH-2591: Test class process isolation with preserving global state and with loaded bootstrap, without global string.
Expected result is to have an error in first test, and then have variable set in second test to be visible in third.
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = __DIR__ . '/2591/bootstrapWithBootstrapNoGlobal.php';
$_SERVER['argv'][4] = __DIR__ . '/2591/SeparateClassPreserveTest.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E.E                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) Issue2591_SeparateClassPreserveTest::testOriginalGlobalString
Undefined index: globalString

%sSeparateClassPreserveTest.php:%d

2) Issue2591_SeparateClassPreserveTest::testGlobalString
Undefined index: globalString

%sSeparateClassPreserveTest.php:%s

ERRORS!
Tests: 3, Assertions: 1, Errors: 2.

