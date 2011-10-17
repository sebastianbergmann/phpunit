--TEST--
phpunit --jobs=10 php://stdout TwoSubsuitesTest ../_files/TwoSubsuitesTest.php
--FILE--
<?php
$_SERVER['argv'][2] = '--jobs=2';
$_SERVER['argv'][3] = 'ParallelismTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/ParallelismTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

..

Time: %i %s, Memory: %sMb

OK (2 tests, 0 assertions)

