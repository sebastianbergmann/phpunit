--TEST--
phpunit --process-isolation --story BowlingGameSpec ../Samples/BowlingGame/BowlingGameSpec.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--story';
$_SERVER['argv'][4] = 'BowlingGameSpec';
$_SERVER['argv'][5] = dirname(__FILE__).'/../../Samples/BowlingGame/BowlingGameSpec.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

The --story functionality is deprecated and will be removed in the future.

The story result printer cannot be used in process isolation.
