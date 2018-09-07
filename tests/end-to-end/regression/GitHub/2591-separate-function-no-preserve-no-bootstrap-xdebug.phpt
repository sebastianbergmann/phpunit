--TEST--
GH-2591: Test method process isolation without preserving global state and without loaded bootstrap.
Expected result is to have an error, because of no classes loaded.
--SKIPIF--
<?php
if (!extension_loaded('xdebug')) {
    print 'skip: xdebug not loaded';
}
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = __DIR__ . '/2591/bootstrapNoBootstrap.php';
$_SERVER['argv'][4] = __DIR__ . '/2591/SeparateFunctionNoPreserveTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) Issue2591_SeparateFunctionNoPreserveTest::testChangedGlobalString
PHPUnit\Framework\Exception: PHP Fatal error:  Class 'PHPUnit\Framework\TestCase' not found %s
PHP Stack trace:
%a

2) Issue2591_SeparateFunctionNoPreserveTest::testGlobalString
PHPUnit\Framework\Exception: PHP Fatal error:  Class 'PHPUnit\Framework\TestCase' not found %s
PHP Stack trace:
%a

ERRORS!
Tests: 2, Assertions: 0, Errors: 2.
