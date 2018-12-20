--TEST--
phpunit --testdox --colors=always --verbose RouterTest ../_files/StatusTest.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--testdox',
    '--colors=always',
    '--verbose',
    \realpath(__DIR__ . '/_files/StatusTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/raw_output_StatusTest.txt
