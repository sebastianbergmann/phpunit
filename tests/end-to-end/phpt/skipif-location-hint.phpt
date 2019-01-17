--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--verbose',
    \realpath(__DIR__ . '/../_files/phpt-skipif-location-hint-example.phpt'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) %stests%eend-to-end%e_files%ephpt-skipif-location-hint-example.phpt
something terrible happened

%stests%eend-to-end%e_files%ephpt-skipif-location-hint-example.phpt:9

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Skipped: 1.
