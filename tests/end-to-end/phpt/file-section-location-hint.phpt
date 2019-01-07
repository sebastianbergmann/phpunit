--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--verbose',
    \realpath(__DIR__ . '/../_files/phpt-file-section-location-hint-example.phpt'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %stests%eend-to-end%e_files%ephpt-file-section-location-hint-example.phpt
Failed asserting that two strings are equal.

/Users/ewout/proj/phpunit/tests/end-to-end/_files/phpt-file-section-location-hint-example.phpt:10

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
