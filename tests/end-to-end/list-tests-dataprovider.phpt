--TEST--
phpunit --list-tests ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderTest.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test(s):
 - DataProviderTest::testAdd#0
 - DataProviderTest::testAdd#1
 - DataProviderTest::testAdd#2
 - DataProviderTest::testAdd#3
