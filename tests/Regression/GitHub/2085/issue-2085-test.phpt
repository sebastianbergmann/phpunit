--TEST--
https://github.com/sebastianbergmann/phpunit/issues/2085
--SKIPIF--
<?php
if (false || !\class_exists(Invoker::class)) {
    print "Package phpunit/php-invoker is required for enforcing time limits");
}
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--enforce-time-limit';
$_SERVER['argv'][3] = '--default-time-limit=1';
$_SERVER['argv'][4] = __DIR__ . '/Issue2085Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) Issue2085Test::testShouldAbortSlowTestByEnforcingTimeLimit
Execution aborted after 1 second

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Risky: 1.
