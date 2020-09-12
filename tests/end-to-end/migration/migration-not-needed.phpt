--TEST--
Configuration migration from current format is not attempted
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--migrate-configuration';

chdir(sys_get_temp_dir());
copy(__DIR__ . '/migration-not-needed/phpunit.xml', 'phpunit.xml');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main(false);

print file_get_contents(sys_get_temp_dir() . '/phpunit.xml');
var_dump(file_exists(sys_get_temp_dir() . '/phpunit.xml.bak'));
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

%sphpunit.xml does not need to be migrated.
