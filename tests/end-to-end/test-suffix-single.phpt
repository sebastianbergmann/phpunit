--TEST--
phpunit --test-suffix .test.php ../../_files/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--test-suffix';
$_SERVER['argv'][] = '.test.php';
$_SERVER['argv'][] = __DIR__ . '/../_files/';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %sBankAccountTest.test.php
               Class name was 'BankAccountWithCustomExtensionTest', expected 'BankAccountTest'

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
