--TEST--
phpunit --testdox-text php://stdout --testdox-exclude-group one ../../_files/TestDoxGroupTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox-text',
    'php://stdout',
    '--testdox-exclude-group',
    'one',
    \realpath(__DIR__ . '/_files/TestDoxGroupTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.Dox Group
.                                                                  2 / 2 (100%) [x] Two



Time: %s, Memory: %s

OK (2 tests, 2 assertions)
