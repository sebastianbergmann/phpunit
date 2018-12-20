--TEST--
phpunit --process-isolation MultiDependencyTest _files/MultiDependencyTest.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--process-isolation',
    'MultiDependencyTest',
    \realpath(__DIR__ . '/_files/MultiDependencyTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK (5 tests, 6 assertions)
