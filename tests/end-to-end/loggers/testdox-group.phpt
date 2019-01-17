--TEST--
phpunit --testdox-text php://stdout --testdox-group one TestDoxGroupTest ../../_files/TestDoxGroupTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox-text',
    'php://stdout',
    '--testdox-group',
    'one',
    'TestDoxGroupTest',
    \realpath(__DIR__ . '/_files/TestDoxGroupTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

DoxGroup
..                                                                  2 / 2 (100%) [x] One



Time: %s, Memory: %s

OK (2 tests, 2 assertions)
