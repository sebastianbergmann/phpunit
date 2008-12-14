--TEST--
phpunit ConcreteTest ../_files/ConcreteTest.php
--FILE--
<?php
$_SERVER['argv'][1] = 'ConcreteTest';
$_SERVER['argv'][2] = dirname(dirname(__FILE__)) . '/_files/ConcreteTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

..

Time: %i seconds

OK (2 tests, 0 assertions)

