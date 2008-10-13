--TEST--
#578: Double printing of trace line for exceptions from notices and warnings
--FILE--
<?php
$_SERVER['argv'][1] = 'Bug578Test';
$_SERVER['argv'][2] = 'Regression/Issue578Test.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

EEE

Time: 0 seconds

There were 3 errors:

1) testNoticesDoublePrintStackTrace(Issue578Test)
Invalid error type specified
%s/Issue578Test.php:9
%s/578.php:5

2) testWarningsDoublePrintStackTrace(Issue578Test)
Invalid error type specified
%s/Issue578Test.php:15
%s/578.php:5

3) testUnexpectedExceptionsPrintsCorrectly(Issue578Test)
Exception: Double printed exception
%s/Issue578Test.php:20
%s/578.php:5

FAILURES!
Tests: 3, Assertions: 0, Errors: 3.
