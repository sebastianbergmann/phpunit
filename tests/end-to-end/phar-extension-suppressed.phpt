--TEST--
phpunit --configuration tests/_files/phar-extension --no-extensions
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must not be loaded.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phar-extension';
$_SERVER['argv'][] = '--no-extensions';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s%ephar-extension%ephpunit.xml

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) ExtensionTest::testOne
Error: Class %sPHPUnit\TestFixture\TestExtension\Test%s not found

%sExtensionTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
