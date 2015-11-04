--TEST--
#1868: Support --stop-on-error long option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--stop-on-error';
$_SERVER['argv'][3] = __DIR__ . '/options/StopOnTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F.ISE

Time: %s ms, Memory: %sMb

There was 1 error:

1) StopOnTest::testShouldBeError
Should error

%s/tests/Regression/GitHub/1868/options/StopOnTest.php:26

--

There was 1 failure:

1) StopOnTest::testShouldFail
Always fail

%s/tests/Regression/GitHub/1868/options/StopOnTest.php:6

FAILURES!
Tests: 5, Assertions: 0, Errors: 1, Failures: 1, Skipped: 1, Incomplete: 1.

