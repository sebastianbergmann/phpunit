--TEST--
#578: Double printing of trace line for exceptions from notices and warnings
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue578Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/578/Issue578Test.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

EEE

Time: %s, Memory: %sMb

There were 3 errors:

1) Issue578Test::testNoticesDoublePrintStackTrace
Invalid error type specified

%s/Issue578Test.php:%i

2) Issue578Test::testWarningsDoublePrintStackTrace
Invalid error type specified

%s/Issue578Test.php:%i

3) Issue578Test::testUnexpectedExceptionsPrintsCorrectly
Exception: Double printed exception

%s/Issue578Test.php:%i

FAILURES!
Tests: 3, Assertions: 0, Errors: 3.
