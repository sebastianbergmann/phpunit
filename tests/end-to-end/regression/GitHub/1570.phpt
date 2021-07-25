--TEST--
https://github.com/sebastianbergmann/phpunit/issues/1570
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = __DIR__ . '/1570/Issue1570Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
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
