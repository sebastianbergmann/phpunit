--TEST--
phpunit BankAccountTest ../../_files/GeneratorTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][3] = __DIR__ . '/../_files/GeneratorTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Test\GeneratorTest::testGenerator
Failed asserting that actual size 3 matches expected size 4.

%sGeneratorTest.php:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
