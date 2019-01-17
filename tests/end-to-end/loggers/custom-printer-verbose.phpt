--TEST--
phpunit -c _files/configuration.custom-printer.xml --verbose IncompleteTest ../../_files/IncompleteTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    \realpath(__DIR__ . '/_files/configuration.custom-printer.xml'),
    '--verbose',
    'IncompleteTest',
    \realpath(__DIR__ . '/../../_files/IncompleteTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sconfiguration.custom-printer.xml

I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 incomplete test:

1) IncompleteTest::testIncomplete
Test incomplete

%s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
