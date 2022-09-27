--TEST--
phpunit --generate-configuration
--STDIN--




--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--generate-configuration';

require_once __DIR__ . '/../../bootstrap.php';
chdir(sys_get_temp_dir());
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Generating phpunit.xml in %s

Bootstrap script (relative to path shown above; default: vendor/autoload.php): Tests directory (relative to path shown above; default: tests): Source directory (relative to path shown above; default: src): Cache directory (relative to path shown above; default: .phpunit.cache): 
Generated phpunit.xml in %s.
Make sure to exclude the .phpunit.cache directory from version control.
