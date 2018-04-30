--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3107
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--testdox';
$_SERVER['argv'][4] = __DIR__ . '/Issue3107Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

 ✘ Call to undefined function %Sdoes_not_exist() in %s:%d
%A
%A
%A
%A
%A
%A
%A

Time: %s, Memory: %s


ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
