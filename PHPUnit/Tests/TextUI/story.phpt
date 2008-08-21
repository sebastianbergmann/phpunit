--TEST--
phpunit --story BowlingGameSpec ../Samples/BowlingGame/BowlingGameSpec.php
--FILE--
<?php
$_SERVER['argv'][1] = '--story';
$_SERVER['argv'][2] = 'BowlingGameSpec';
$_SERVER['argv'][3] = '../Samples/BowlingGame/BowlingGameSpec.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

BowlingGameSpec
 - Score for one spare is 16 [successful]

   Given New game
    When Player rolls
     and Player rolls
     and Player rolls
    Then Score should be

Scenarios: 1, Failed: 0, Skipped: 0, Incomplete: 0.

