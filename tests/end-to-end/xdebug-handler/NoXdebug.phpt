--TEST--
phpunit NoXdebugWhenLoadedTest.php or NoXdebugWhenNotLoadedTest.php
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    print 'skip: PHP runtime required';
}
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

$testEnv = 'PHPUNIT_XDEBUG_HANDLER_TEST';
$loadedTest = 'NoXdebugWhenLoadedTest.php';

if (getenv($testEnv)) {
    // We are in a restart
    $file = $loadedTest;
} elseif (extension_loaded('xdebug')) {
    // We will be restarted
    $file = $loadedTest;
    putenv($testEnv . '=1');
} else {
    // Check if we have been restarted prior to this test
    if (PHPUnit\Util\XdebugManager::getRestartSettings()) {
        $file = $loadedTest;
    } else {
        $file = 'NoXdebugWhenNotLoadedTest.php';
    }
}

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2]  = '--no-xdebug';
$_SERVER['argv'][3]  = __DIR__ . '/' . $file;

PHPUnit\TextUI\Command::main();
putenv($testEnv);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 2 assertions)
