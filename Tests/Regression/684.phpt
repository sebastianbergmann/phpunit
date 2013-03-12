--TEST--
#684: Unable to find test class when no test methods exists
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue684Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/684/Issue684Test.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

F

Time: %s, Memory: %sMb

There was 1 failure:

1) Warning
No tests found in class "Foo_Bar_Issue684Test".


FAILURES!
Tests: 1, Assertions: 0, Failures: 1.
