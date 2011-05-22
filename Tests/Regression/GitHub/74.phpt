--TEST--
GH-74: catchable fatal error in 3.5
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = 'Issue74Test';
$_SERVER['argv'][4] = dirname(__FILE__).'/74/Issue74Test.php';

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

E

Time: %i %s, Memory: %sMb

There was 1 error:

1) Issue74Test::testCreateAndThrowNewExceptionInProcessIsolation
NewException: Testing GH-74

%s/Tests/Regression/GitHub/74/Issue74Test.php:7
%s

FAILURES!
Tests: 1, Assertions: 0, Errors: 1.
