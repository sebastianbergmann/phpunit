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
 [x] Score for gutter game is 0

   Given New game 
    Then Score should be 0

 [x] Score for all ones is 20

   Given New game 
    When Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
     and Player rolls 1
    Then Score should be 20

 [x] Score for one spare and 3 is 16

   Given New game 
    When Player rolls 5
     and Player rolls 5
     and Player rolls 3
    Then Score should be 16

 [x] Score for one strike and 3 and 4 is 24

   Given New game 
    When Player rolls 10
     and Player rolls 3
     and Player rolls 4
    Then Score should be 24

 [x] Score for perfect game is 300

   Given New game 
    When Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
     and Player rolls 10
    Then Score should be 300

Scenarios: 5, Failed: 0, Skipped: 0, Incomplete: 0.
