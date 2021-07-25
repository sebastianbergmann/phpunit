--TEST--
phpunit -c _files/configuration.custom-printer.xml --verbose ../../_files/IncompleteTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/configuration.custom-printer.xml');
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/IncompleteTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sconfiguration.custom-printer.xml

I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 incomplete test:

1) PHPUnit\TestFixture\IncompleteTest::testIncomplete
Test incomplete

%s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
