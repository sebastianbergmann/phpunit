--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/DependentOfTestWhichReturnsSomethingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..SS                                                                4 / 4 (100%)

Time: %s, Memory: %s MB

1 test triggered 1 PHPUnit warning:

1) DependentOfTestWhichReturnsSomethingTest::test2
This test depends on "DependentOfTestWhichReturnsSomethingTest::test1" which returns a value. Such test cannot be run in repeat mode

%s/tests/end-to-end/repeat/_files/DependentOfTestWhichReturnsSomethingTest.php:%d

OK, but there were issues!
Tests: 4, Assertions: 2, PHPUnit Warnings: 1, Skipped: 2.
