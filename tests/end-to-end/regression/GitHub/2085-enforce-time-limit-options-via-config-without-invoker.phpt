--TEST--
Test XML config enforceTimeLimit, defaultTimeLimit without php-invoker, with pcntl
--SKIPIF--
<?php
require __DIR__ . '/../../../bootstrap.php';

if (\class_exists(SebastianBergmann\Invoker\Invoker::class)) {
    print "Skip: package phpunit/php-invoker is installed" . PHP_EOL;
}

if (!\extension_loaded('pcntl') || \strpos(\ini_get('disable_functions'), 'pcntl') !== false) {
    print "Skip: extension pcntl is required for enforcing time limits" . PHP_EOL;
}
--DESCRIPTION--
https://github.com/sebastianbergmann/phpunit/issues/2085
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '-c';
$_SERVER['argv'][2] = __DIR__ . '/2085/configuration_enforce_time_limit_options.xml';
$_SERVER['argv'][3] = __DIR__ . '/2085/Issue2085Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Error:         Package phpunit/php-invoker is required for enforcing time limits
.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 2 assertions)
