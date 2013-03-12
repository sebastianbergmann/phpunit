--TEST--
phpunit BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'BankAccountTest';
$_SERVER['argv'][3] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...

Time: %s, Memory: %sMb

OK (3 tests, 3 assertions)
