--TEST--
Configuration migration from PHPUnit 8.5 format works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--migrate-configuration';

chdir(sys_get_temp_dir());
copy(__DIR__ . '/migration-from-85/phpunit-8.5.xml', 'phpunit.xml');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Created backup:         %sphpunit.xml.bak
Migrated configuration: %sphpunit.xml
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/phpunit.xml');
unlink(sys_get_temp_dir() . '/phpunit.xml.bak');
