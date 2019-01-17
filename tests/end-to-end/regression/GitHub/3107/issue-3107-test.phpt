--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3107
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--testdox';
$_SERVER['argv'][4] = '--order-by=no-depends';
$_SERVER['argv'][5] = __DIR__ . '/Issue3107Test.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Issue3107\Issue3107
 ✘ One
   │
   │ Error: Call to undefined function %Sdoes_not_exist()
   │
   │ %sIssue3107Test.php:%d
   │

Time: %s, Memory: %s


ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
