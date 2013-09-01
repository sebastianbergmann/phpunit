--TEST--
phpunit --process-isolation --filter testTrue#3 DataProviderFilterTest ../_files/DataProviderFilterTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--filter';
$_SERVER['argv'][4] = 'testTrue#3';
$_SERVER['argv'][5] = 'DataProviderFilterTest';
$_SERVER['argv'][6] = dirname(dirname(__FILE__)) . '/_files/DataProviderFilterTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

.

Time: %i %s, Memory: %sMb

OK (1 test, 1 assertion)
