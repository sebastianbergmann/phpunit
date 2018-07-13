--TEST--
Support --check-version option.
--FILE--
<?php
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--check-version';

require __DIR__ . '/../bootstrap.php';
define('__PHPUNIT_PHAR__', '');
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

You are using the latest version of PHPUnit.
