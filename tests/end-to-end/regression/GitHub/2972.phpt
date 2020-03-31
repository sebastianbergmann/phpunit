--TEST--
GH-2972: Test suite shouldn't fail when it contains both *.phpt files and unconventionally named tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/2972/';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %s/unconventiallyNamedIssue2972Test.php
               Class name was 'Issue2972Test', expected 'unconventiallyNamedIssue2972Test'

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
