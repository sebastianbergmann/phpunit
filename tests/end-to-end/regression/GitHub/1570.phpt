--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1570
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--disallow-test-output';
$_SERVER['argv'][3] = __DIR__ . '/1570/Issue1570Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)*

Time: %s, Memory: %s

There was 1 risky test:

1) Issue1570Test::testOne
This test did not perform any assertions

%s:14

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Risky: 1.
