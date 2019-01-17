--TEST--
phpunit StackTest _files/StackTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    'StackTest',
    \realpath(__DIR__ . '/_files/StackTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 5 assertions)
