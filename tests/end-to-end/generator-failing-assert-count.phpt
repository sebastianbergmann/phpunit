--TEST--
phpunit BankAccountTest ../../_files/FailingGeneratorCountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][3] = __DIR__ . '/../_files/FailingGeneratorCountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Test\FailingGeneratorCountTest::testGenerator
Failed asserting that actual size 3 matches expected size 4.

%sFailingGeneratorCountTest.php:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
