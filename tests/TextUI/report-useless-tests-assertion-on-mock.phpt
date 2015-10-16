--TEST--
phpunit --report-useless-tests AssertionOnMockTest --verbose ../_files/AssertionOnMockTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--report-useless-tests';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = 'AssertionOnMockTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/AssertionOnMockTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

R.R                                                                 3 / 3 (100%)

Time: %s, Memory: %sMb

There were 2 risky tests:

1) AssertionOnMockTest::testOne
This test performed an assertion on a test double

%s/AssertionOnMockTest.php:15

2) AssertionOnMockTest::testThree
This test performed an assertion on a test double

%s/AssertionOnMockTest.php:27

OK, but incomplete, skipped, or risky tests!
Tests: 3, Assertions: 1, Risky: 2.
