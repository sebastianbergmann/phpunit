--TEST--
phpunit --exclude-group=foo ../../_files/DataProviderIssue2922/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--exclude-group=foo';
$_SERVER['argv'][3] = __DIR__ . '/../_files/DataProviderIssue2922/';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %s/DataProviderIssue2922/SecondTest.php
               Class name was 'SecondHelloWorldTest', expected 'SecondTest'

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
