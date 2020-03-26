--TEST--
phpunit ../../_files/InternalProphecyIntegrationTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/../_files/InternalProphecyIntegrationTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 warning:

1) PHPUnit\TestFixture\InternalProphecyIntegrationTest::testOne
PHPUnit\Framework\TestCase::prophesize() is deprecated and will be removed in PHPUnit 10. Please use the trait provided by phpspec/prophecy-phpunit.

WARNINGS!
Tests: 1, Assertions: 2, Warnings: 1.
