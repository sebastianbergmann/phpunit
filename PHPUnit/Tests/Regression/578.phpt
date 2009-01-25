--TEST--
#578: Double printing of trace line for exceptions from notices and warnings
--FILE--
<?php
$_SERVER['argv'][1] = 'Issue578Test';
$_SERVER['argv'][2] = 'Regression/Issue578Test.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

EEE

Time: %i seconds

There were 3 errors:

1) testNoticesDoublePrintStackTrace(Issue578Test)
Invalid error type specified
%s/Issue578Test.php:%i
%s/578.php:%i

2) testWarningsDoublePrintStackTrace(Issue578Test)
Invalid error type specified
%s/Issue578Test.php:%i
%s/578.php:%i

3) testUnexpectedExceptionsPrintsCorrectly(Issue578Test)
Exception: Double printed exception
%s/Issue578Test.php:%i
%s/578.php:%i

FAILURES!
Tests: 3, Assertions: 0, Errors: 3.
