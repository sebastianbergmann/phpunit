--TEST--
phpunit ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/force-covers-annotation/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/force-covers-annotation/tests/Test.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) Test::testOne
This test does not have a @covers annotation but is expected to have one

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Risky: 1.
