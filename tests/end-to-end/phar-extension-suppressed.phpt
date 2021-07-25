--TEST--
phpunit --configuration tests/_files/phpunit-example-extension --no-extensions
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must not be loaded.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpunit-example-extension';
$_SERVER['argv'][] = '--no-extensions';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Fatal error: Trait %sPHPUnit\ExampleExtension\TestCaseTrait%s not found in %s
