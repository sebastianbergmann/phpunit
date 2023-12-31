--TEST--
phpunit --list-groups --exclude-filter name
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-groups';
$_SERVER['argv'][] = '--exclude-filter';
$_SERVER['argv'][] = 'name';

require_once __DIR__ . '/../../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The --exclude-filter and --list-groups options cannot be combined, --exclude-filter is ignored

Available test group(s):

