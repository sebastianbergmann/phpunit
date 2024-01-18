--TEST--
Configuration migration is not possible when the configuration file does not validate against any known schema
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--migrate-configuration';

chdir(sys_get_temp_dir());
copy(__DIR__ . '/_files/unsupported-schema/phpunit.xml', 'phpunit.xml');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Created backup:   %sphpunit.xml.bak
Migration failed: %s does not validate against any know schema
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/phpunit.xml');
unlink(sys_get_temp_dir() . '/phpunit.xml.bak');
