--TEST--
#797: assert bootstraps included in code running in separate process
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][4] = '--process-isolation';
$_SERVER['argv'][5] = 'Issue797Test';
$_SERVER['argv'][6] = dirname(__FILE__).'/797/Issue797Test.php';

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

.

Time: %i %s, Memory: %sMb

OK (1 test, 2 assertions)
