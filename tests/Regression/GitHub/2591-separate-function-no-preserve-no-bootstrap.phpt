--TEST--
GH-2591: Test method process isolation without preserving global state and without loaded bootstrap.
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = __DIR__ . '/2591/bootstrapNoBootstrap.php';
$_SERVER['argv'][4] = __DIR__ . '/2591/SeparateFunctionNoPreserveTest.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) Issue2591Test::testChangedGlobalString
PHPUnit\Framework\Exception: PHP Fatal error:  Class 'PhpUnit\Framework\TestCase' not found in %s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php on line 8
PHP Stack trace:
PHP   1. {main}() -:0
PHP   2. __phpunit_run_isolated_test() -:108
PHP   3. require_once() -:30

Fatal error: Class 'PhpUnit\Framework\TestCase' not found in %s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php on line 8

Call Stack:
    %s     %s   1. {main}() -:0
    %s     %s   2. __phpunit_run_isolated_test() -:108
    %s     %s   3. require_once('%s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php') -:30

2) Issue2591Test::testGlobalString
PHPUnit\Framework\Exception: PHP Fatal error:  Class 'PhpUnit\Framework\TestCase' not found in %s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php on line 8
PHP Stack trace:
PHP   1. {main}() -:0
PHP   2. __phpunit_run_isolated_test() -:108
PHP   3. require_once() -:30

Fatal error: Class 'PhpUnit\Framework\TestCase' not found in %s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php on line 8

Call Stack:
    %s     %s   1. {main}() -:0
    %s     %s   2. __phpunit_run_isolated_test() -:108
    %s     %s   3. require_once('%s/tests/Regression/GitHub/2591/SeparateFunctionNoPreserveTest.php') -:30

ERRORS!
Tests: 2, Assertions: 0, Errors: 2.