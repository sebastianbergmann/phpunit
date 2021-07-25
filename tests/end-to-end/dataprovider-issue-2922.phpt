--TEST--
phpunit --exclude-group=foo ../../_files/DataProviderIssue2922/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--exclude-group=foo';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderIssue2922/';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


Warning:       Test case class not matching filename is deprecated
               in %sSecondTest.php
               Class name was 'SecondHelloWorldTest', expected 'SecondTest'

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
