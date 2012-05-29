--TEST--
phpunit --testsuite Parent::Child1 ParentSuite ../_files/ParentSuite.php
--FILE--
<?php

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testsuite';
$_SERVER['argv'][] = 'Parent::Child1';
$_SERVER['argv'][] = 'ParentSuite';
$_SERVER['argv'][] = dirname(__FILE__).'/../_files/ParentSuite.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit @package_version@ by Sebastian Bergmann.

F

Time: 0 seconds, Memory: 4.50Mb

There was 1 failure:

1) OneTest::testSomething
Failed asserting that false is true.

%s/OneTest.php:%i
%s/testsuite-filter.php:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
