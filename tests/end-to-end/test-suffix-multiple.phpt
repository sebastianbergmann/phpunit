--TEST--
phpunit --test-suffix .test.php,.my.php ../../_files/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--test-suffix';
$_SERVER['argv'][3] = '.test.php,.my.php';
$_SERVER['argv'][4] = __DIR__ . '/../_files/';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %s/BankAccountTest.test.php
               Class name was 'BankAccountWithCustomExtensionTest', expected 'BankAccountTest'
Warning:       Test case class not matching filename is deprecated
               in %s/ConcreteTest.my.php
               Class name was 'ConcreteWithMyCustomExtensionTest', expected 'ConcreteTest'

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK (5 tests, 5 assertions)
