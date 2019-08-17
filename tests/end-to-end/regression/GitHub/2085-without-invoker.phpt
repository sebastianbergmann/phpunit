--TEST--
Test CLI flags --enforce-time-limit --default-time-limit without php-invoker, with pcntl
--DESCRIPTION--
https://github.com/sebastianbergmann/phpunit/issues/2085
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../../bootstrap.php';

if (\class_exists(SebastianBergmann\Invoker\Invoker::class)) {
    print "Skip: package phpunit/php-invoker is installed" . PHP_EOL;
}

if (!\extension_loaded('pcntl') || \strpos(\ini_get('disable_functions'), 'pcntl') !== false) {
    print "Skip: extension pcntl is required for enforcing time limits" . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--enforce-time-limit';
$_SERVER['argv'][3] = '--default-time-limit=10';
$_SERVER['argv'][4] = __DIR__ . '/2085/Issue2085Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Error:         Package phpunit/php-invoker is required for enforcing time limits
.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 2 assertions)
