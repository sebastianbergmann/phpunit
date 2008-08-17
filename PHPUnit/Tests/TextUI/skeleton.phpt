--TEST--
phpunit Calculator ../_files/Calculator.php
--FILE--
<?php
$_SERVER['argv'][1] = 'Calculator';
$_SERVER['argv'][2] = dirname(__DIR__) . '/_files/Calculator.php';

require_once dirname(dirname(__DIR__)) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

....

Time: %i seconds

OK (4 tests, 4 assertions)
