--TEST--
GH-1351: Test result does not serialize test class in process isolation
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = 'Issue1351Test';
$_SERVER['argv'][4] = __DIR__ . '/1351/Issue1351Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

F.E.

Time: %s, Memory: %sMb

There was 1 error:

1) Issue1351Test::testExceptionPre
RuntimeException: Expected rethrown exception.
%A
Caused by
LogicException: Expected exception.
%A

--

There was 1 failure:

1) Issue1351Test::testFailurePre
Expected failure.
%A
FAILURES!
Tests: 4, Assertions: 5, Failures: 1, Errors: 1.