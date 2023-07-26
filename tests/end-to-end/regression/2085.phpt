--TEST--
Test CLI flags --enforce-time-limit --default-time-limit
--DESCRIPTION--
https://github.com/sebastianbergmann/phpunit/issues/2085
--SKIPIF--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

if (!\class_exists(SebastianBergmann\Invoker\Invoker::class)) {
    print "Skip: package phpunit/php-invoker is required for enforcing time limits" . PHP_EOL;
}

if (!\extension_loaded('pcntl') || \strpos(\ini_get('disable_functions'), 'pcntl') !== false) {
    print "Skip: extension pcntl is required for enforcing time limits" . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = '--default-time-limit=1';
$_SERVER['argv'][] = __DIR__ . '/2085/Issue2085Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\Issue2085Test::testShouldAbortSlowTestByEnforcingTimeLimit
This test was aborted after 1 second

%s:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
