--TEST--
phpunit --testdox --colors=always --verbose -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    realpath(__DIR__ . '/../../basic/configuration.basic.xml'),
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
