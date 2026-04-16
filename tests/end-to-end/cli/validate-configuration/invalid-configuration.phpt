--TEST--
phpunit --validate-configuration with invalid configuration file
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--validate-configuration';

chdir(sys_get_temp_dir());
copy(__DIR__ . '/_files/invalid/phpunit.xml', 'phpunit.xml');

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

XML configuration file %sphpunit.xml does not validate against the PHPUnit %s schema:

  Line %d:
  - %s

  Line %d:
  - %s
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/phpunit.xml');
