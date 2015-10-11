--TEST--
#1868: Support --check-version option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--check-version';

require __DIR__ . '/../../../bootstrap.php';
define('__PHPUNIT_PHAR__', '');
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

You are not using the latest version of PHPUnit.
Use "phpunit --self-update" to install PHPUnit %s
