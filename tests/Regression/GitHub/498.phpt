--TEST--
GH-498: The test methods won't be run if a dataProvider throws Exception and --group is added in command line
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--group';
$_SERVER['argv'][3] = 'trueOnly';
$_SERVER['argv'][4] = 'Issue498Test';
$_SERVER['argv'][5] = dirname(__FILE__).'/498/Issue498Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

F

Time: %s, Memory: %sMb

There was 1 failure:

1) Warning
The data provider specified for Issue498Test::shouldBeFalse is invalid.
Can't create the data

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.
