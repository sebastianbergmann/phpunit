--TEST--
phpunit --help
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--help'
    ];
array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
output-cli-usage.txt
