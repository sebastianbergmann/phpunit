--TEST--
phpunit --group one --list-groups ../../_files/listing-tests-and-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = '--list-groups';
$_SERVER['argv'][] = __DIR__ . '/../../_files/listing-tests-and-groups';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test group:
 - one (1 test)
