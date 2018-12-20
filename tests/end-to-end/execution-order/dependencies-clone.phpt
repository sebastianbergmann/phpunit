--TEST--
phpunit --verbose ClonedDependencyTest ../../_files/ClonedDependencyTest.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--verbose',
    'ClonedDependencyTest',
    \realpath(__DIR__ . '/_files/ClonedDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

OK (6 tests, 6 assertions)

