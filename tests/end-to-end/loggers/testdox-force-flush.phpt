--TEST--
phpunit --testdox --colors=never -c tests/basic/configuration.basic.xml --filter Success
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    realpath(__DIR__ . '/../../basic/configuration.basic.xml'),
    '--testdox',
    '--colors=never',
    '--filter', 'Success'
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test result status with and without message
 ✔ Success
 ✔ Success with message

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
