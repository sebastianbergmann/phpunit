--TEST--
phpunit --list-tests ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--list-tests';
$_SERVER['argv'][3] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test(s):
 - PHPUnit\TestFixture\DataProviderTest::testAdd#0
 - PHPUnit\TestFixture\DataProviderTest::testAdd#1
 - PHPUnit\TestFixture\DataProviderTest::testAdd#2
 - PHPUnit\TestFixture\DataProviderTest::testAdd#3
