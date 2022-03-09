--TEST--
phpunit ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/force-covers-annotation/phpunit.xml';
$_SERVER['argv'][] = __DIR__ . '/force-covers-annotation/tests/Test.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\Test::testOne
This test does not define a code coverage target using an attribute or annotation but is expected to do so

%s:%d

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Risky: 1.
