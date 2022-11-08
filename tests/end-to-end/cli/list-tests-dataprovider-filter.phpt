--TEST--
phpunit --list-tests --filter testAdd#0 ../../_files/DataProvider/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testAdd#0';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProvider/DataProviderTest.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

The --filter and --list-tests options cannot be combined, --filter is ignored

Available test(s):
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#0
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#1
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#2
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#3
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#4
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#5
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#6
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#7
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#8
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#9
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#10
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#11
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#12
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#13
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#14
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#15
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#16
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#17
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#18
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#19
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#20
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#21
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#22
 - PHPUnit\TestFixture\DataProvider\DataProviderTest::testAdd#23

