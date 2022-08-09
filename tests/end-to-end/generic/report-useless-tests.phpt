--TEST--
phpunit ../../_files/NothingTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/NothingTest.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test is considered risky for 1 reason:

1) PHPUnit\TestFixture\NothingTest::testNothing
This test did not perform any assertions

%s:%d

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Risky: 1.
