--TEST--
phpunit --configuration tests/_files/phpunit-example-extension --no-extensions
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must not be loaded.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/../_files/phpunit-example-extension';
$_SERVER['argv'][3] = '--no-extensions';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Fatal error: Trait %sPHPUnit\ExampleExtension\TestCaseTrait%s not found in %s
