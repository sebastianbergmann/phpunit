--TEST--
phpunit --testdox -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    realpath(__DIR__ . '/../../basic/configuration.basic.xml'),
    '--testdox',
    '--colors=never',
    realpath(__DIR__ . '/../../unit/Util/TestDox/ColorTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Basic ANSI color highlighting support
 ✔ Colorize with no color
 ✔ Colorize with one color
 ✔ Colorize with multiple colors
 ✔ Colorize with invalid color
 ✔ Colorize with valid and invalid colors
 ✔ Colorize path %ephp%eunit%etest.phpt after NULL
 ✔ Colorize path %ephp%eunit%etest.phpt after ''
 ✔ Colorize path %ephp%eunit%etest.phpt after %e
 ✔ Colorize path %ephp%eunit%etest.phpt after %ephp%e
 ✔ Colorize path %e_d-i.r%et-e_s.t.phpt after ''
 ✔ dim($m) and colorize('dim',$m) return different ANSI codes
 ✔ Visualize all whitespace characters in no-spaces
 ✔ Visualize all whitespace characters in  space   invaders
 ✔ Visualize all whitespace characters in 	indent, space and \n
\r
 ✔ Visualize whitespace but ignore EOL
 ✔ Prettify unnamed dataprovider with data set #0
 ✔ Prettify unnamed dataprovider with data set #1
 ✔ Prettify named dataprovider with data set "one"
 ✔ Prettify named dataprovider with data set "two"
 ✔ TestDox shows name of data set one with value 1
 ✔ TestDox shows name of data set two with value 2

Time: %s, Memory: %s

OK (21 tests, 21 assertions)
