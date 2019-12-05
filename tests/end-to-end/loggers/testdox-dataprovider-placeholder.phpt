--TEST--
phpunit --testdox --colors=always --verbose ../unit/Util/TestDox/ColorTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox',
    '--colors=always',
    '--verbose',
    realpath(__DIR__ . '/../../unit/Util/ColorTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/raw_output_ColorTest.txt
