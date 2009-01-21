--TEST--
phpunit --verbose DependencyTestSuite ../_files/DependencyTestSuite.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--verbose';
$_SERVER['argv'][3] = 'DependencyTestSuite';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/DependencyTestSuite.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

Test Dependencies
 DependencySuccessTest
 ...

 DependencyFailureTest
 FSS

Time: %d seconds

There was 1 failure:

1) testOne(DependencyFailureTest)
%s/dependencies.php:%i

There were 2 skipped tests:

1) testTwo(DependencyFailureTest)
This test depends on "DependencyFailureTest::testOne" to pass.
%s/dependencies.php:%i

2) testThree(DependencyFailureTest)
This test depends on "DependencyFailureTest::testTwo" to pass.
%s/dependencies.php:%i

FAILURES!
Tests: 6, Assertions: 0, Failures: 1, Skipped: 2.
