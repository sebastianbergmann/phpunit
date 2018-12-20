--TEST--
phpunit --testdox --colors=always --verbose RouterTest ../unit/Util/TestDox/ColorTest.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--testdox',
    '--colors=always',
    '--verbose',
    realpath(__DIR__ . '/../../unit/Util/TestDox/ColorTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/raw_output_ColorTest.txt
