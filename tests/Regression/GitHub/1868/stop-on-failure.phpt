--TEST--
#1868: Support --stop-on-failure long option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--stop-on-failure';
$_SERVER['argv'][3] = __DIR__ . '/options/StopOnTest.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F

Time: %s ms, Memory: %sMb

There was 1 failure:

1) StopOnTest::testShouldFail
Always fail

%s/tests/Regression/GitHub/1868/options/StopOnTest.php:6

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.

