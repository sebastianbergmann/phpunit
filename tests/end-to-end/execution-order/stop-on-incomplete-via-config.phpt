--TEST--
phpunit -c ../_files/configuration_stop_on_incomplete.xml ./tests/_files/StopOnErrorTestSuite.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    \realpath(__DIR__ . '/../../_files/configuration_stop_on_incomplete.xml'),
    \realpath(__DIR__ . '/../../_files/StopOnErrorTestSuite.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

I

Time: %s, Memory: %s

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
