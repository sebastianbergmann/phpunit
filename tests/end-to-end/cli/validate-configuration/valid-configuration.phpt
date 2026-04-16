--TEST--
phpunit --validate-configuration with valid configuration file
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--validate-configuration';

chdir(sys_get_temp_dir());
copy(__DIR__ . '/_files/valid/phpunit.xml', 'phpunit.xml');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

XML configuration file %sphpunit.xml is valid
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/phpunit.xml');
