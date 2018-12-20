--TEST--
phpunit --order-by=default,foobar
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--debug',
    '--order-by=default,foobar',
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

unrecognized --order-by option: foobar
