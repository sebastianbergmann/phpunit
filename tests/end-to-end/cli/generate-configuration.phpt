--TEST--
phpunit --generate-configuration
--STDIN--



--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--generate-configuration';

require __DIR__ . '/../../bootstrap.php';
chdir(sys_get_temp_dir());
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Generating phpunit.xml in %s

Bootstrap script (relative to path shown above; default: vendor/autoload.php): Tests directory (relative to path shown above; default: tests): Source directory (relative to path shown above; default: src): 
Generated phpunit.xml in %s
