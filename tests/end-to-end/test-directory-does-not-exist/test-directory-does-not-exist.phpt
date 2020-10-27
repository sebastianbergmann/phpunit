--TEST--
An error is emitted when a configured test directory does not exist
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test directory "%stest-directory-does-not-exist%stests" not found
