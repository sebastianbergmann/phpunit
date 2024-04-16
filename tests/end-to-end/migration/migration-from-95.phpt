--TEST--
Configuration migration from PHPUnit 9.5 format works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--migrate-configuration';

$originalPHPUnitXMLFile = __DIR__ . '/_files/migration-from-95/phpunit-9.5.xml';
$phpunitXMLFile = sys_get_temp_dir() . '/phpunit.xml';

chdir(sys_get_temp_dir());
copy($originalPHPUnitXMLFile, $phpunitXMLFile);

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

assert(
    file_get_contents($originalPHPUnitXMLFile) === file_get_contents($phpunitXMLFile)
);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Created backup:         %sphpunit.xml.bak
Migrated configuration: %sphpunit.xml
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/phpunit.xml');
unlink(sys_get_temp_dir() . '/phpunit.xml.bak');
