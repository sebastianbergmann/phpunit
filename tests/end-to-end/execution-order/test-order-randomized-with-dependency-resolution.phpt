--TEST--
phpunit --order-by=depends,random ../_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--verbose',
    '--resolve-dependencies',     // keep coverage for legacy CLI option
    '--order-by=depends,random',
    'MultiDependencyTest',
    \realpath(__DIR__ . '/../execution-order/_files/MultiDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random seed:   %d

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK (5 tests, 6 assertions)
