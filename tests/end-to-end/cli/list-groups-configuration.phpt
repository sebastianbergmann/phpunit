--TEST--
phpunit --list-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/groups';
$_SERVER['argv'][] = '--list-groups';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test group(s):
 - default
 - one
 - two
