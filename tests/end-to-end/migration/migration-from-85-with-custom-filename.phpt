--TEST--
Configuration migration from PHPUnit 8.5 format works with custom filename
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = 'custom.xml';
$_SERVER['argv'][] = '--migrate-configuration';

$originalPHPUnitXMLFile = __DIR__ . '/_files/migration-from-85/phpunit-8.5.xml';
$phpunitXMLFile = sys_get_temp_dir() . '/custom.xml';

chdir(sys_get_temp_dir());
copy($originalPHPUnitXMLFile, $phpunitXMLFile);

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

assert(
    file_get_contents($originalPHPUnitXMLFile) === file_get_contents($phpunitXMLFile)
);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Created backup:         %scustom.xml.bak
Migrated configuration: %scustom.xml
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/custom.xml');
unlink(sys_get_temp_dir() . '/custom.xml.bak');
